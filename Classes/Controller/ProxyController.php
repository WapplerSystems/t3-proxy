<?php

namespace WapplerSystems\Proxy\Controller;


use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 *
 */
class ProxyController extends ActionController
{

    public function processAction(): ResponseInterface
    {

        DebugUtility::debug($this->settings);

        $html = '';


        return $this->htmlResponse($html);
    }


}
