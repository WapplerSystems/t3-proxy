<?php

namespace WapplerSystems\Proxy\Controller;


use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\ImmediateResponseException;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Frontend\Controller\ErrorController;
use WapplerSystems\Proxy\Http\Request;
use WapplerSystems\Proxy\Plugin\ImagePlugin;
use WapplerSystems\Proxy\Plugin\LinkPlugin;
use WapplerSystems\Proxy\Plugin\ProxifyPlugin;
use WapplerSystems\Proxy\Plugin\Typo3CssPlugin;
use WapplerSystems\Proxy\Proxy;
use WapplerSystems\Proxy\Typo3Cache;

/**
 *
 */
class ProxyController extends ActionController
{

    /**
     * @param string $path
     * @return ResponseInterface
     * @throws \Exception
     */
    public function processAction(string $path = ''): ResponseInterface
    {

        DebugUtility::debug($this->settings);
        DebugUtility::debug('TEST: '.$path);

        DebugUtility::debug($this->request->getUri());

        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $uriBuilder->setTargetPageUid($GLOBALS['TSFE']->id)->setCreateAbsoluteUri(true);
        $localBaseUri = $uriBuilder->buildFrontendUri();

        $url = $this->settings['startUrl'];
        $pathPrefix = $this->settings['pathPrefix'];
        $baseUrl = $this->settings['baseUrl'];
        if ($path !== '') {
            $url = $baseUrl.$path;
        }

        DebugUtility::debug($url);
        $request = new Request('GET', $url);

        $proxy = GeneralUtility::makeInstance(Proxy::class);
        $proxy->setLocalBaseUri($localBaseUri);
        $proxy->setBaseUrl($baseUrl);
        $proxy->setPathPrefix($pathPrefix);

        //$proxy->addSubscriber(new ProxifyPlugin());
        $proxy->addSubscriber(new Typo3CssPlugin(['oxygen-webhelp/app/main-page.css', 'oxygen-webhelp/template/oxygen.css']));
        $proxy->addSubscriber(new ImagePlugin());
        $proxy->addSubscriber(new LinkPlugin());

        $response = $proxy->forward($request);
        DebugUtility::debug($response->getStatusCode());
        if ($response->getStatusCode() !== 200) {

            $message = 'No entry found!';
            $response = GeneralUtility::makeInstance(ErrorController::class)->pageNotFoundAction(
                $GLOBALS['TYPO3_REQUEST'],
                $message
            );
            throw new ImmediateResponseException($response, 1590468229);

        }


        $html = $response->getBody();


        return $this->htmlResponse('<!-- proxy start -->' . $html . '<!-- proxy end -->');
    }


}
