<?php
declare(strict_types=1);

/*
 * This file is part of the "proxy" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace WapplerSystems\Proxy\Plugin;


use WapplerSystems\Proxy\Config;
use WapplerSystems\Proxy\Event\ProxyEvent;

class CorsPlugin extends AbstractPlugin
{
    public function onBeforeRequest(ProxyEvent $event)
    {

        $request = $event['request'];

        $urlParts = parse_url($request->getUri());

        $url = $urlParts['scheme'] . '://' . $urlParts['host'];

        $request->headers->set('Access-Control-Allow-Origin', '*');
        $request->headers->set('Access-Control-Allow-Credentials', 'true');
        $request->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $request->headers->set('Access-Control-Allow-Headers', 'DNT,Origin,X-CustomHeader,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type');

        $request->headers->set('Origin', $url, true);

        if (!Config::get('no_referer')) {
            $request->headers->set('Referer', $url, true);
        }
    }
}
