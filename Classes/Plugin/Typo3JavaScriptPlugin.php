<?php
declare(strict_types=1);

/*
 * This file is part of the "proxy" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace WapplerSystems\Proxy\Plugin;


use PHPHtmlParser\Dom\Node\HtmlNode;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WapplerSystems\Proxy\Event\ProxyEvent;
use WapplerSystems\Proxy\Http\Request;
use WapplerSystems\Proxy\Http\Response;

class Typo3JavaScriptPlugin extends AbstractAssetPlugin
{

    public function onCompleted(ProxyEvent $event)
    {
        $this->whiteList = explode("\n", $this->settings['js']['whitelist'] ?? '');

        /** @var Response $response */
        $response = $event['response'];
        /** @var Request $request */
        $request = $event['request'];

        /** @var HtmlNode $head */
        $scripts = $response->getDom()->find('script');

        /** @var HtmlNode $script */
        foreach ($scripts as $script) {
            $src = $script->getAttribute('src');
            if ($this->isOnWhiteList($src)) {
                $attributes = [];
                $src = $this->proxy->makeAbsoluteUrl($src);
                foreach ($script->getAttributes() as $name => $attribute) {
                    if (str_starts_with($name, 'data-')) {
                        if (str_ends_with($attribute, '.js')) {
                            $attribute = $this->proxy->makeAbsoluteUrl($attribute);
                        }
                        $attributes[$name] = $attribute;
                    }
                }
                GeneralUtility::makeInstance(AssetCollector::class)->addJavaScript(md5($src), $src, $attributes);

            }
        }

    }


}
