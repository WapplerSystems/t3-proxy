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
use WapplerSystems\Proxy\Plugin\AbstractPlugin;
use WapplerSystems\Proxy\Plugin\ConfigurablePlugin;
use WapplerSystems\Proxy\Plugin\ImagePlugin;
use WapplerSystems\Proxy\Plugin\LinkPlugin;
use WapplerSystems\Proxy\Plugin\Typo3JavaScriptPlugin;
use WapplerSystems\Proxy\Plugin\Typo3TitlePlugin;
use WapplerSystems\Proxy\Proxy;

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

        //DebugUtility::debug($this->settings);
        //DebugUtility::debug('Path: '.$path);

        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $uriBuilder->setTargetPageUid($GLOBALS['TSFE']->id)->setCreateAbsoluteUri(true);
        $localBaseUri = $uriBuilder->buildFrontendUri();

        $url = $this->settings['startUrl'];
        $pathPrefix = $this->settings['pathPrefix'];
        $baseUrl = $this->settings['baseUrl'];
        if ($path !== '') {
            $url = $baseUrl.$path;
        }

        $request = new Request('GET', $url);

        $proxy = GeneralUtility::makeInstance(Proxy::class);
        $proxy->setLocalBaseUri($localBaseUri);
        $proxy->setBaseUrl($baseUrl);
        $proxy->setPathPrefix($pathPrefix);


        $pluginNames = explode(',',$this->settings['plugins'] ?? '');

        foreach ($pluginNames as $pluginName) {
            if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['proxy']['plugins'][$pluginName])) {
                $proxy->addSubscriber(GeneralUtility::makeInstance($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['proxy']['plugins'][$pluginName], $this->settings));
            }
        }


        $response = $proxy->forward($request);
        //DebugUtility::debug($response->getStatusCode());
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
