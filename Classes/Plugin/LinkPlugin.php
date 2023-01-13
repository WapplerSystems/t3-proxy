<?php
declare(strict_types=1);

/*
 * This file is part of the "proxy" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace WapplerSystems\Proxy\Plugin;


use PhpParser\Node;
use WapplerSystems\Proxy\Event\ProxyEvent;
use WapplerSystems\Proxy\Http\Request;
use WapplerSystems\Proxy\Http\Response;

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
            $link->setAttribute('href', $href);
        }

        $links = $response->getDom()->find('form');
        /** @var Node $link */
        foreach ($links as $link) {
            $href = $link->getAttribute('action');
            $href = $this->proxy->rewriteURL($href);
            $link->setAttribute('action', $href);
        }
    }

}
