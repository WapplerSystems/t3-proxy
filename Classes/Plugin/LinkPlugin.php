<?php

namespace WapplerSystems\Proxy\Plugin;


use PhpParser\Node;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WapplerSystems\Proxy\Event\ProxyEvent;
use WapplerSystems\Proxy\Http\Request;
use WapplerSystems\Proxy\Http\Response;
use WapplerSystems\Proxy\Proxy;

class LinkPlugin extends AbstractPlugin
{


    public function onCompleted(ProxyEvent $event)
    {

        /** @var Response $response */
        $response = $event['response'];
        /** @var Request $request */
        $request = $event['request'];

        $links = $response->getDom()->find('a');
        /** @var Node $link */
        foreach ($links as $link) {
            $href = $link->getAttribute('href');
            $href = $this->proxy->rewriteURL($href);
            $link->setAttribute('href',$href);
        }

        $links = $response->getDom()->find('form');
        /** @var Node $link */
        foreach ($links as $link) {
            $href = $link->getAttribute('action');
            $href = $this->proxy->rewriteURL($href);
            $link->setAttribute('action',$href);
        }
    }

}
