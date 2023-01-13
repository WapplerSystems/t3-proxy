<?php
declare(strict_types=1);

/*
 * This file is part of the "proxy" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace WapplerSystems\Proxy\Plugin;


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
            $title = $title[0]->innerHtml;

            $suffix = $this->settings['pageTitleSuffix'] ?? null;
            if ($suffix !== null) {
                $title .= $suffix;
            }
            $titleProvider->setTitle($title);
        }

    }

}
