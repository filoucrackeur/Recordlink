<?php
defined('TYPO3_MODE') or die();

if (TYPO3_MODE === 'BE') {

    // register record browsers
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ElementBrowsers']['record'] =  \Intera\Recordlink\Browser\RecordBrowser::class;

    // register record link handlers
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('
		TCEMAIN.linkHandler {
			record {
				handler = Intera\\Recordlink\\LinkHandler\\RecordLinkHandler
				label = LLL:EXT:recordlink/Resources/Private/Language/locallang_be.xlf:record
				scanAfter = page
			}
		}
	');

}
