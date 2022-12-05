<?php

namespace WapplerSystems\Proxy\Plugin;


use PHPHtmlParser\Dom\Node\HtmlNode;
use TYPO3\CMS\Core\MetaTag\MetaTagManagerRegistry;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WapplerSystems\Proxy\Event\ProxyEvent;
use WapplerSystems\Proxy\Http\Request;
use WapplerSystems\Proxy\Http\Response;
use WapplerSystems\Proxy\PageTitle\ProxyPageTitleProvider;

class Typo3MetaPlugin extends AbstractPlugin
{


    public function onCompleted(ProxyEvent $event)
    {

        $whiteList = explode("\n",$this->settings['meta']['whitelist'] ?? '');

        /** @var Response $response */
        $response = $event['response'];
        /** @var Request $request */
        $request = $event['request'];

        $metaNodes = $response->getDom()->find('meta');
        /** @var HtmlNode $metaNode */
        foreach ($metaNodes as $metaNode) {
            if (in_array($metaNode->getAttribute('name'),$whiteList)) {
                $metaTagManager = GeneralUtility::makeInstance(MetaTagManagerRegistry::class)->getManagerForProperty($metaNode->getAttribute('name'));
                $metaTagManager->addProperty($metaNode->getAttribute('name'), $metaNode->getAttribute('content'));
            }
        }
    }
}
