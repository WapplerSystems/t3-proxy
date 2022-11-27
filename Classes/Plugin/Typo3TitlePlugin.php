<?php

namespace WapplerSystems\Proxy\Plugin;


use PhpParser\Node;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WapplerSystems\Proxy\Event\ProxyEvent;
use WapplerSystems\Proxy\Http\Request;
use WapplerSystems\Proxy\Http\Response;
use WapplerSystems\Proxy\PageTitle\ProxyPageTitleProvider;

class Typo3TitlePlugin extends AbstractPlugin
{

    public function onCompleted(ProxyEvent $event)
    {

        /** @var Response $response */
        $response = $event['response'];
        /** @var Request $request */
        $request = $event['request'];

        $title = $response->getDom()->find('title');
        if (isset($title[0])) {
            $titleProvider = GeneralUtility::makeInstance(ProxyPageTitleProvider::class);
            $titleProvider->setTitle($title[0]->innerHtml);
        }

    }

}
