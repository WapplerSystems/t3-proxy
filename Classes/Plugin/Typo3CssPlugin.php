<?php

namespace WapplerSystems\Proxy\Plugin;


use PhpParser\Node;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WapplerSystems\Proxy\Event\ProxyEvent;
use WapplerSystems\Proxy\Http\Request;
use WapplerSystems\Proxy\Http\Response;

class Typo3CssPlugin extends AbstractAssetPlugin
{

    private $base_url = '';



    private function css_url($matches)
    {

        $url = trim($matches[1]);
        if (starts_with($url, 'data:')) {
            return $matches[0];
        }

        return str_replace($matches[1], proxify_url($matches[1], $this->base_url), $matches[0]);
    }

    // this.params.logoImg&&(e="background-image: url("+this.params.logoImg+")")
    private function css_import($matches)
    {
        return str_replace($matches[2], proxify_url($matches[2], $this->base_url), $matches[0]);
    }


    // The <body> background attribute is not supported in HTML5. Use CSS instead.
    private function proxify_css($str)
    {

        // The HTML5 standard does not require quotes around attribute values.

        // if {1} is not there then youtube breaks for some reason
        $str = preg_replace_callback('@[^a-z]{1}url\s*\((?:\'|"|)(.*?)(?:\'|"|)\)@im', [$this, 'css_url'], $str);

        // https://developer.mozilla.org/en-US/docs/Web/CSS/@import
        // TODO: what about @import directives that are outside <style>?
        $str = preg_replace_callback('/@import (\'|")(.*?)\1/i', [$this, 'css_import'], $str);

        return $str;
    }

    public function onCompleted(ProxyEvent $event)
    {

        /** @var Response $response */
        $response = $event['response'];
        /** @var Request $request */
        $request = $event['request'];

        $links = $response->getDom()->find('link[rel="stylesheet"]');
        /** @var Node $link */
        foreach ($links as $link) {
            $href = $link->getAttribute('href');
            if ($this->isOnWhiteList($href)) {
                $href = $this->proxy->sanitizeURL($href);

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
