<?php
if (!defined ('TYPO3_MODE')) {
  die ('Access denied.');
}


// Register linkhandler for "record"
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['typolinkLinkHandler']['record']
    = 'Intera\Recordlink\Hooks\LinkHandler';



// Deprecated: Remove from TYPO3 7.6

// Register hooks
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/class.browse_links.php']['browseLinksHook']['record']
    = 'Intera\Recordlink\Hooks\ElementBrowser';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/rtehtmlarea/mod3/class.tx_rtehtmlarea_browse_links.php']['browseLinksHook']['record']
    = 'Intera\Recordlink\Hooks\ElementBrowser';
