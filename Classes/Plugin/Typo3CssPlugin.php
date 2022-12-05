<?php

namespace WapplerSystems\Proxy\Plugin;


use PhpParser\Node;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WapplerSystems\Proxy\Event\ProxyEvent;
use WapplerSystems\Proxy\Http\Request;
use WapplerSystems\Proxy\Http\Response;

class Typo3CssPlugin extends AbstractAssetPlugin
{

    public function onCompleted(ProxyEvent $event)
    {
        $this->whiteList = explode("\n",$this->settings['css']['whitelist'] ?? '');

        /** @var Response $response */
        $response = $event['response'];
        /** @var Request $request */
        $request = $event['request'];

        $links = $response->getDom()->find('link[rel="stylesheet"]');
        /** @var Node $link */
        foreach ($links as $link) {
            $href = $link->getAttribute('href');
            if ($this->isOnWhiteList($href)) {
                $href = $this->proxy->makeAbsoluteUrl($href);

                /*
                if (!$this->proxy->getCache()->has($href)) {

                    $file = GeneralUtility::getUrl($href);

                    $this->proxy->getCache()->set($cacheIdentifier,$file);

                    DebugUtility::debug('not in cache');
                }*/

                GeneralUtility::makeInstance(AssetCollector::class)->addStyleSheet(md5($href),$href);

                //$requestHost = parse_url($this->request->getUri())['host'];

                //DebugUtility::debug($href);

            }


        }


        //$str = $this->proxify_css($str);


    }



}
