<?php
if (!defined('TYPO3')) {
    die('Access denied.');
}

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

ExtensionUtility::registerPlugin(
    'proxy',
    'proxy',
    'Proxy',
    null,
    'plugins',
    '',
    'FILE:EXT:proxy/Configuration/FlexForms/Settings.xml'
);
