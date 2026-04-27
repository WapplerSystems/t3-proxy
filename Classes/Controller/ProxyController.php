<?php
declare(strict_types=1);

/*
 * This file is part of the "proxy" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace WapplerSystems\Proxy\Controller;


use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Http\ImmediateResponseException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Frontend\Controller\ErrorController;
use WapplerSystems\Proxy\Http\Request;
use WapplerSystems\Proxy\Proxy;

/**
 *
 */
class ProxyController extends ActionController implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @param string $path
     * @return ResponseInterface
     * @throws \Exception
     */
    public function processAction(string $path = ''): ResponseInterface
    {

        $this->uriBuilder->setTargetPageUid($GLOBALS['TSFE']->id)->setCreateAbsoluteUri(true);
        $localBaseUri = $this->uriBuilder->buildFrontendUri();

        $url = $this->settings['startUrl'];
        $baseUrl = $this->settings['baseUrl'];
        if ($path !== '') {
            $url = $baseUrl . $path;
        }

        $this->logger?->info('ProxyController processAction start', [
            'path' => $path,
            'resolvedUrl' => $url,
            'baseUrl' => $baseUrl,
            'localBaseUri' => $localBaseUri,
            'pageUid' => $GLOBALS['TSFE']->id,
        ]);

        $request = new Request('GET', $url);

        $proxy = GeneralUtility::makeInstance(Proxy::class);
        $proxy->setLocalBaseUri($localBaseUri);
        $proxy->setBaseUrl($baseUrl);

        // Configure cache TTL from settings (default: 3600 seconds)
        $cacheTtl = (int)($this->settings['cacheTtl'] ?? 3600);
        $proxy->setCacheTtl($cacheTtl);

        $ipResolveSetting = strtolower(trim((string)($this->settings['ipResolve'] ?? '')));
        $ipResolve = match ($ipResolveSetting) {
            'v4', 'ipv4', '4' => CURL_IPRESOLVE_V4,
            'v6', 'ipv6', '6' => CURL_IPRESOLVE_V6,
            default => null,
        };
        $proxy->setIpResolve($ipResolve);

        $pluginNames = explode(',', $this->settings['plugins'] ?? '');

        foreach ($pluginNames as $pluginName) {
            if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['proxy']['plugins'][$pluginName])) {
                $proxy->addSubscriber(GeneralUtility::makeInstance($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['proxy']['plugins'][$pluginName], $this->settings));
            }
        }

        try {
            $response = $proxy->forward($request);
        } catch (\Exception $e) {
            $this->logger?->error('Proxy request failed', [
                'url' => $url,
                'error' => $e->getMessage(),
                'exceptionClass' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            $errorResponse = GeneralUtility::makeInstance(ErrorController::class)->pageNotFoundAction(
                $GLOBALS['TYPO3_REQUEST'],
                'Proxy request failed'
            );
            throw new ImmediateResponseException($errorResponse, 1590468229);
        }

        if ($response->getStatusCode() !== 200) {
            $this->logger?->warning('Proxy received non-200 response', [
                'url' => $url,
                'statusCode' => $response->getStatusCode(),
                'responseHeaders' => $response->headers->all(),
                'contentLength' => strlen($response->getContent()),
            ]);

            $message = 'No entry found!';
            $errorResponse = GeneralUtility::makeInstance(ErrorController::class)->pageNotFoundAction(
                $GLOBALS['TYPO3_REQUEST'],
                $message
            );
            throw new ImmediateResponseException($errorResponse, 1590468229);
        }

        $html = $response->getBody();

        $this->logger?->info('ProxyController processAction success', [
            'url' => $url,
            'statusCode' => $response->getStatusCode(),
            'bodyLength' => strlen($html),
            'contentType' => $response->headers->get('content-type'),
        ]);

        return $this->htmlResponse('<!-- proxy start -->' . $html . '<!-- proxy end -->');
    }

}