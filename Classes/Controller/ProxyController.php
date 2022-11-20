<?php

namespace WapplerSystems\Proxy\Controller;


use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use WapplerSystems\Proxy\Http\Request;
use WapplerSystems\Proxy\Plugin\ProxifyPlugin;
use WapplerSystems\Proxy\Plugin\Typo3CssPlugin;
use WapplerSystems\Proxy\Proxy;
use WapplerSystems\Proxy\Typo3Cache;

/**
 *
 */
class ProxyController extends ActionController
{

    public function processAction(): ResponseInterface
    {

        DebugUtility::debug($this->settings);


        $url = $this->settings['url'];
        $baseUrl = $this->settings['baseUrl'];

        $request = new Request('GET',$url);

        $proxy = GeneralUtility::makeInstance(Proxy::class);
        $proxy->setBaseUrl($baseUrl);

        $proxy->setCache(new Typo3Cache());

        //$proxy->addSubscriber(new ProxifyPlugin());
        $typo3CssPlugin = new Typo3CssPlugin();
        $typo3CssPlugin->addToWhiteList(['oxygen-webhelp/app/main-page.css']);

        $proxy->addSubscriber($typo3CssPlugin);

        $response = $proxy->forward($request);

        $html = $response->getBody();


        return $this->htmlResponse('<!-- proxy start -->'.$html.'<!-- proxy end -->');
    }


}
