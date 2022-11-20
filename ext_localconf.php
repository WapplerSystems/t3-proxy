<?php

use TYPO3\CMS\Core\Cache\Backend\FileBackend;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use WapplerSystems\Proxy\Controller\ProxyController;

if (!defined('TYPO3')) {
    die ('Access denied.');
}


ExtensionUtility::configurePlugin(
    'proxy',
    'proxy',
    [
        ProxyController::class => 'process',
    ],
    [
    ]
);


$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['proxy_assets'] ??= [];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['proxy_assets']['backend'] ??= FileBackend::class;



