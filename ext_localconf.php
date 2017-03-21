<?php
if (!defined ('TYPO3_MODE')) {
  die ('Access denied.');
}

// Register linkhandler for "record"
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['typolinkLinkHandler']['record']
    = 'Intera\Recordlink\Hooks\LinkHandler';
