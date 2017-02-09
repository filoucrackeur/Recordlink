<?php
namespace Intera\Recordlink\LinkHandler;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Recordlist\LinkHandler\AbstractLinkHandler;
use TYPO3\CMS\Recordlist\LinkHandler\LinkHandlerInterface;
use TYPO3\CMS\Recordlist\Controller\AbstractLinkBrowserController;
use TYPO3\CMS\Recordlist\Tree\View\LinkParameterProviderInterface;

/**
 * Link handler for record links
 */
class RecordLinkHandler extends AbstractLinkHandler implements LinkHandlerInterface, LinkParameterProviderInterface
{

    /**
     * Parts of the current link
     *
     * @var array
     */
    protected $linkParts = [];

    /**
     * Initialize the handler
     *
     * @param AbstractLinkBrowserController $linkBrowser
     * @param string $identifier
     * @param array $configuration Page TSconfig
     *
     * @return void
     */
    public function initialize(AbstractLinkBrowserController $linkBrowser, $identifier, array $configuration)
    {
        parent::initialize($linkBrowser, $identifier, $configuration);
        $this->configuration = $configuration;
        $this->config = GeneralUtility::_GP('config');
        $this->searchString = GeneralUtility::_GP('search_field');
        $this->pointer = intval(GeneralUtility::_GP('pointer'));
    }

    /**
     * Checks if this is the handler for the given link
     *
     * The handler may store this information locally for later usage.
     *
     * @param array $linkParts Link parts as returned from TypoLinkCodecService
     *
     * @return bool
     */
    public function canHandleLink(array $linkParts)
    {

        if (!$linkParts['url']) {
            return false;
        }

        list($handler, $config, $uid) = explode(':', $linkParts['url']);

        if ($handler != 'record') {
            return false;
        }


        if (isset($this->configuration[$config . '.']['table'])) {
            $table = $this->configuration[$config.'.']['table'];
        }

        $this->linkParts = $linkParts;
        $this->linkParts['act'] = 'record';
        $this->linkParts['info'] = $this->configuration[$config.'.']['label'];
        $this->linkParts['config'] = $config;
        $this->linkParts['recordTable'] = $table;
        $this->linkParts['recordUid'] = $uid;

        return true;
    }

    /**
     * Format the current link for HTML output
     *
     * @return string
     */
    public function formatCurrentUrl()
    {
        $label = $this->linkParts['info'];
        $table = $this->linkParts['recordTable'];
        $uid = $this->linkParts['recordUid'];
        $record = BackendUtility::getRecordWSOL($table, $uid);
        $title = BackendUtility::getRecordTitle($table, $record, FALSE, TRUE);
        $titleLen = (int)$this->getBackendUser()->uc['titleLen'];
        $title = GeneralUtility::fixed_lgd_cs($title, $titleLen);

        return $label . ' \'' . $title . '\' (ID:' . $uid . ')';
    }

    /**
     * Render the link handler
     *
     * @param ServerRequestInterface $request
     *
     * @return string
     */
    public function render(ServerRequestInterface $request)
    {
        GeneralUtility::makeInstance(PageRenderer::class)->loadRequireJsModule('Intera/Recordlink/RecordLinkHandler');

        $recordselector = $this->getRecordSelector();
        $recordlist = $this->getRecordList();
        $content = '';
        $content .= '

				<!--
					Wrapper table for record Selector:
				-->
						<table border="0" cellpadding="0" cellspacing="0" id="typo3-EBrecords">
							<tr>
								<td class="c-wCell" valign="top">' . $recordselector . $recordlist . '</td>
							</tr>
						</table>
						';

        return $content;

    }

    /**
     * @return string[] Array of body-tag attributes
     */
    public function getBodyTagAttributes()
    {
        if (empty($this->linkParts)) {
            return [];
        }

        $config = $this->linkParts['config'];
        $uid = $this->linkParts['recordUid'];
        $addPassOnParams = '&config=' . $config;

        return [
            'data-current-link' => empty($this->linkParts) ? '' : 'record:' . $config . ':' . $uid,
            'data-add-on-params' => '&act=record'
        ];
    }

    /**
     * @param array $values Array of values to include into the parameters or which might influence the parameters
     *
     * @return string[] Array of parameters which have to be added to URLs
     */
    public function getUrlParameters(array $values)
    {
        $parameters = [
        ];
        return array_merge($this->linkBrowser->getUrlParameters($values), $parameters);
    }

    /**
     * @param array $values Values to be checked
     *
     * @return bool Returns TRUE if the given values match the currently selected item
     */
    public function isCurrentlySelectedItem(array $values)
    {
        return !empty($this->linkParts) && (int)$this->linkParts['pageid'] === (int)$values['pid'];
    }

    /**
     * Returns the URL of the current script
     *
     * @return string
     */
    public function getScriptUrl()
    {
        return $this->linkBrowser->getScriptUrl();
    }

    // *********
    // Internal:
    // *********

    protected function getRecordSelector() {
        $out = '';
        $out .= '<h3>'
            . $GLOBALS['LANG']->sL('LLL:EXT:recordlink/Resources/Private/Language/locallang_be.xlf:select_linktype')
            . ':</h3>';
        $out .= '<div class="form-group">';
        $onChange = 'onchange="jumpToUrl(' . GeneralUtility::quoteJSvalue('?act=record&config=') . ' + this.value); return false;"';
        $out .= '<select class="form-control" ' . $onChange . ' >';
        $out .= '<option value=""></option>';
        if (empty($this->config)) {
            $this->config = $this->linkParts['config'];
        }
        foreach ($this->configuration as $key => $config) {
            $key = substr($key, 0, -1);
            if($key==$this->config) {
                $out .= '<option value="'.$key.'" selected="selected">'.$config['label'].'</option>';
            } else {
                $out .= '<option value="'.$key.'">'.$config['label'].'</option>';
            }
        }
        $out .= '</select>';
        $out .= '</div>';
        return $out;
    }

    protected function getRecordList() {
        $out = '';
        $table = $this->configuration[$this->config . '.']['table'];
        $id = intval($this->configuration[$this->config . '.']['pid']);
        $pointer = $this->pointer;
        $recursive = intval($this->configuration[$this->config . '.']['recursive']);
        if (empty($recursive)) {
            $recursive = 1;
        }
        if ($table) {
            $elementBrowser = GeneralUtility::makeInstance(\TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList::class);
            $elementBrowser->start($id, $table, $pointer,
                $this->searchString,
                $recursive,
                10
            );
            $elementBrowser->setDispFields('pid');
            $elementBrowser->pidSelect = '1=1';
            $elementBrowser->disableSingleTableView = TRUE;
            $elementBrowser->clickMenuEnabled = FALSE;
            $elementBrowser->noControlPanels = TRUE;
            $elementBrowser->searchLevels = FALSE;
            $elementBrowser->generateList();

            $list = $elementBrowser->getTable($table, $id, $GLOBALS['TCA'][$table]['ctrl']['label']);

            if (empty($list)) {
                $out .= '<div class="alert alert-info">'
                    . $GLOBALS['LANG']->sL('LLL:EXT:recordlink/Resources/Private/Language/locallang_be.xlf:norecords_found')
                    . '</div>';
            } else {
                $out .= $list;
            }
            //linkWrapItems
            //$out .= $elementBrowser->getSearchBox();
            $out .= $this->getSearchBox($elementBrowser);

        }

        return $out;
    }

    protected function getSearchBox($elementBrowser) {

        $formElements = array('<form action="' . htmlspecialchars($elementBrowser->listURL() . '&act=record&config='.$this->config) . '" method="post" style="padding:0;">', '</form>');

        // Table with the search box:
        $content = '<div class="db_list-searchbox-form">
			' . $formElements[0] . '

				<!--
					Search box:
				-->
				<table border="0" cellpadding="0" cellspacing="0" id="typo3-dblist-search">
					<tr>
						<td style="padding-right:10px;">' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xlf:labels.enterSearchString', TRUE) . '</td>
						<td><input type="text" name="search_field" id="search_field" value="' . htmlspecialchars($this->searchString) . '" style="width: 100%; height: 23px;" /></td>
						<td><input type="submit" name="search" value="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xlf:labels.search', TRUE) . '" /></td>
					</tr>
				</table>
			' . $formElements[1]
            . '</div>'
            . '<br><br>';
        return $content;
    }

}
