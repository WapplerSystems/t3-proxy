<?php

namespace WapplerSystems\Proxy\Controller;


use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use WapplerSystems\Proxy\Http\Request;
use WapplerSystems\Proxy\Plugin\ProxifyPlugin;
use WapplerSystems\Proxy\Proxy;

/**
 *
 */
class ProxyController extends ActionController
{

    public function processAction(): ResponseInterface
    {

        DebugUtility::debug($this->settings);


        $url = $this->settings['url'];

        $request = new Request('GET',$url);
        $proxy = new Proxy();

        $proxy->addSubscriber(new ProxifyPlugin());

        $response = $proxy->forward($request,$url);



        return $this->htmlResponse('<!-- proxy start -->'.$html.'<!-- proxy end -->');
    }


}
