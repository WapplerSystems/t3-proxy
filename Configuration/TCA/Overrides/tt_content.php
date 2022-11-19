<?php
if (!defined('TYPO3')) {
    die('Access denied.');
}

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

ExtensionUtility::registerPlugin(
    'proxy',
    'proxy',
    'Proxy'
);

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['proxy_proxy'] = 'pages,layout,select_key,recursive';

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['proxy_proxy'] = 'pi_flexform';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'proxy_proxy',
    'FILE:EXT:proxy/Configuration/FlexForms/Settings.xml'
);
