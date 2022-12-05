<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use WapplerSystems\Proxy\Controller\ProxyController;
use WapplerSystems\Proxy\Plugin\ImagePlugin;
use WapplerSystems\Proxy\Plugin\LinkPlugin;
use WapplerSystems\Proxy\Plugin\Typo3CssPlugin;
use WapplerSystems\Proxy\Plugin\Typo3JavaScriptPlugin;
use WapplerSystems\Proxy\Plugin\Typo3MetaPlugin;
use WapplerSystems\Proxy\Plugin\Typo3TitlePlugin;

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


//$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['proxy_assets'] ??= [];
//$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['proxy_assets']['backend'] ??= \WapplerSystems\Proxy\Cache\Backend\AssetFileBackend::class;


$GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['aspects']['PathMapper'] = \WapplerSystems\Proxy\Routing\Aspect\PathMapper::class;


ExtensionManagementUtility::addTypoScriptSetup(trim('
    config.pageTitleProviders {
        proxy {
            provider = WapplerSystems\Proxy\PageTitle\ProxyPageTitleProvider
            before = altPageTitle,record,seo
        }
    }'));

if (!isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['proxy']['plugins'])) {
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['proxy']['plugins'] = [];
}
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['proxy']['plugins'] = array_merge($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['proxy']['plugins'],[
  'Typo3JavaScriptPlugin' => Typo3JavaScriptPlugin::class,
  'ImagePlugin' => ImagePlugin::class,
  'LinkPlugin' => LinkPlugin::class,
  'Typo3TitlePlugin' => Typo3TitlePlugin::class,
  'Typo3CssPlugin' => Typo3CssPlugin::class,
  'Typo3MetaPlugin' => Typo3MetaPlugin::class,
]);



