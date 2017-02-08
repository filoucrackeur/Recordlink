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
use TYPO3\CMS\Recordlist\LinkHandler\AbstractLinkHandler;
use TYPO3\CMS\Recordlist\LinkHandler\LinkHandlerInterface;

use Intera\Recordlink\Browser\RecordBrowser;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Tree\View\ElementBrowserPageTreeView;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
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

        list($handler, $recordTable, $recordId) = explode(':', $linkParts['url']);

        if ($handler != 'record') {
            return false;
        }
        // TODO: check $recordTable
        // TODO: check $recordId

        $this->linkParts = $linkParts;
        $this->linkParts['table'] = $recordTable;
        $this->linkParts['recordid'] = $recordId;

        return true;
    }

    /**
     * Format the current link for HTML output
     *
     * @return string
     */
    public function formatCurrentUrl()
    {
        // TODO: check config
        $table = $this->linkParts['table'];
        $uid = $this->linkParts['recordid'];
        $record = BackendUtility::getRecordWSOL($table, $uid);
        $title = BackendUtility::getRecordTitle($table, $record, FALSE, TRUE);
        $titleLen = (int)$this->getBackendUser()->uc['titleLen'];
        $title = GeneralUtility::fixed_lgd_cs($title, $titleLen);

        return '\'' . $title . '\' (' . $table . ':' . $uid . ')';
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
        GeneralUtility::makeInstance(PageRenderer::class)->loadRequireJsModule('Intera/Recordlink/LinkHandler/RecordLinkHandler');

        $backendUser = $this->getBackendUser();

        $recordselector = 'TODO: selector';
        $recordlist = 'TODO: recordlist';

        $content = '';
        $content .= '

				<!--
					Wrapper table for record Selector:
				-->
						<table border="0" cellpadding="0" cellspacing="0" id="typo3-linkFiles">
							<tr>
								<td class="c-wCell" valign="top"><h3>' . $GLOBALS['LANG']->sL('LLL:EXT:recordlink/Resources/Private/Language/locallang_be.xlf:select_record') . ':</h3>' . $recordselector . $recordlist . '</td>
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

        $table = $this->linkParts['table'];
        $uid = $this->linkParts['recordid'];

        return [
            'data-current-link' => 'record:' . $table . ':' . $uid
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

    /**
     * @param string[] $fieldDefinitions Array of link attribute field definitions
     * @return string[]
     */
    public function modifyLinkAttributes(array $fieldDefinitions)
    {
        $configuration = $this->linkBrowser->getConfiguration();
        if (!empty($configuration['pageIdSelector.']['enabled'])) {
            array_push($this->linkAttributes, 'pageIdSelector');
            $fieldDefinitions['pageIdSelector'] = '
				<tr>
					<td>
						<label>
							' . $this->getLanguageService()->getLL('page_id', true) . ':
						</label>
					</td>
					<td colspan="3">
						<input type="text" size="6" name="luid" id="luid" /> <input class="btn btn-default t3js-pageLink" type="submit" value="'
            . $this->getLanguageService()->getLL('setLink', true) . '" />
					</td>
				</tr>';
        }
        return $fieldDefinitions;
    }


}
