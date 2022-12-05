<?php

namespace WapplerSystems\Proxy\Plugin;


use PhpParser\Node;
use WapplerSystems\Proxy\Event\ProxyEvent;
use WapplerSystems\Proxy\Http\Request;
use WapplerSystems\Proxy\Http\Response;

class ImagePlugin extends AbstractAssetPlugin
{

    public function onCompleted(ProxyEvent $event)
    {

        /** @var Response $response */
        $response = $event['response'];
        /** @var Request $request */
        $request = $event['request'];

        $images = $response->getDom()->find('img');
        /** @var Node $imageNode */
        foreach ($images as $imageNode) {
            $src = $imageNode->getAttribute('src');
            if ($src === null) {
                continue;
            }
            $src = $this->proxy->makeAbsoluteUrl($src);
            $imageNode->setAttribute('src',$src);
        }
    }

}
