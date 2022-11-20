<?php

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

