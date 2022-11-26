<?php

namespace WapplerSystems\Proxy;

use Exception;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use WapplerSystems\Proxy\Event\ProxyEvent;
use WapplerSystems\Proxy\Http\Request;
use WapplerSystems\Proxy\Http\Response;

class Proxy
{

    // Proxy script version
    const VERSION = '5.2.0';

    private $dispatcher;

    private Request $request;
    private Response $response;

    private bool $outputBuffering = true;
    private ?string $outputBuffer = '';

    private FrontendInterface $cache;

    private bool $statusFound = false;

    private string $baseUrl;
    private string $localBaseUri;
    private string $pathPrefix;

    public function __construct(FrontendInterface $cache)
    {
        $this->cache = $cache;
    }

    public function setOutputBuffering($outputBuffering)
    {
        $this->outputBuffering = $outputBuffering;
    }

    private function header_callback($ch, $headers)
    {
        $parts = explode(":", $headers, 2);

        // extract status code
        // if using proxy - we ignore this header: HTTP/1.1 200 Connection established
        if (preg_match('/HTTP\/[\d.]+\s*(\d+)/', $headers, $matches) && stripos($headers, '200 Connection established') === false) {

            $this->response->setStatusCode($matches[1]);
            $this->statusFound = true;

        } else if (count($parts) === 2) {

            $name = strtolower($parts[0]);
            $value = trim($parts[1]);

            // this must be a header: value line
            $this->response->headers->set($name, $value, false);

        } else if ($this->statusFound) {

            // this is hacky but until anyone comes up with a better way...
            $event = new ProxyEvent(['request' => $this->request, 'response' => $this->response, 'proxy' => &$this]);

            // this is the end of headers - last line is always empty - notify the dispatcher about this
            $this->dispatch('request.sent', $event);
        }

        return strlen($headers);
    }

    private function write_callback($ch, $str)
    {

        $len = strlen($str);

        $this->dispatch('curl.callback.write', new ProxyEvent([
            'request' => $this->request,
            'data' => $str
        ]));

        // Do we buffer this piece of data for later output or not?
        if ($this->outputBuffering) {
            $this->outputBuffer .= $str;
        }

        return $len;
    }

    // TODO: move this all into its own Dispatcher class?
    // https://github.com/guzzle/guzzle/blob/5.3/src/Event/Emitter.php
    // https://github.com/laravel/framework/blob/5.0/src/Illuminate/Events/Dispatcher.php#L72
    private $listeners = [];

    // Proxy instance itself acts as a dispatcher!
    public function getEventDispatcher()
    {
        return $this;
    }

    public function addListener($event, $callback, $priority = 0)
    {
        $this->listeners[$event][$priority][] = $callback;
    }

    public function addSubscriber($subscriber)
    {
        if (method_exists($subscriber, 'subscribe')) {
            $subscriber->subscribe($this);
        }
    }

    private function dispatch($event_name, $event)
    {

        if (isset($this->listeners[$event_name])) {
            $temp = (array)$this->listeners[$event_name];

            foreach ($temp as $priority => $listeners) {
                foreach ((array)$listeners as $listener) {
                    if (is_callable($listener)) {
                        $listener($event);
                    }
                }
            }
        }
    }

    /**
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function forward(Request $request)
    {

        // prepare request and response objects
        $this->request = $request;
        $this->response = new Response();

        $options = [
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 0,

            // don't return anything - we have other functions for that
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_HEADER => false,

            // don't bother with ssl
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,

            // we will take care of redirects
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_AUTOREFERER => false
        ];

        // this is probably a good place to add custom curl options that way other critical options below would overwrite that
        $config_options = Config::get('curl', []);

        $options = Helpers::array_merge($options, $config_options);

        $options[CURLOPT_HEADERFUNCTION] = [$this, 'header_callback'];
        $options[CURLOPT_WRITEFUNCTION] = [$this, 'write_callback'];

        // Notify any listeners that the request is ready to be sent, and this is your last chance to make any modifications.
        $this->dispatch('request.before_send', new ProxyEvent([
            'request' => $this->request,
            'response' => $this->response
        ]));

        // We may not even need to send this request if response is already available somewhere (CachePlugin)
        if ($this->request->params->has('request.complete')) {
            // do nothing?
        } else {

            // any plugin might have changed our URL by this point
            $options[CURLOPT_URL] = $this->request->getUri();

            // fill in the rest of cURL options
            $options[CURLOPT_HTTPHEADER] = explode("\r\n", $this->request->getRawHeaders());
            $options[CURLOPT_CUSTOMREQUEST] = $this->request->getMethod();
            $options[CURLOPT_POSTFIELDS] = $this->request->getRawBody();

            $ch = curl_init();
            curl_setopt_array($ch, $options);

            // fetch the status - if exception if throw any at callbacks, then the error will be supressed
            $result = @curl_exec($ch);

            // there must have been an error if at this point
            if (!$result) {
                $error = sprintf('(%d) %s', curl_errno($ch), curl_error($ch));
                throw new Exception($error);
            }

            // we have output waiting in the buffer?
            $this->response->setContent($this->outputBuffer);

            // saves memory I would assume?
            $this->outputBuffer = null;
        }


        $this->dispatch('request.complete', new ProxyEvent([
            'request' => $this->request,
            'response' => $this->response
        ]));

        return $this->response;
    }


    public function getCache(): FrontendInterface
    {
        return $this->cache;
    }


    public function sanitizeURL($url) : string {
        $host = parse_url($url)['host'];
        if ($host === null) {
            // relative path
            $url = $this->baseUrl.$url;
            $url = str_replace('/./','/',$url);
        }
        return $url;
    }


    public function rewriteURL($url) : string {
        $urlParts = parse_url($url);

        if ($urlParts['host'] === null) {
            // relative path
            $url = $this->localBaseUri.$this->pathPrefix.$url;
            $url = str_replace('/./','/',$url);
        }
        return $url;
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * @param string $baseUrl
     */
    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * @return string
     */
    public function getLocalBaseUri(): string
    {
        return $this->localBaseUri;
    }

    /**
     * @param string $localBaseUri
     */
    public function setLocalBaseUri(string $localBaseUri): void
    {
        $this->localBaseUri = $localBaseUri;
    }

    /**
     * @return string
     */
    public function getPathPrefix(): string
    {
        return $this->pathPrefix;
    }

    /**
     * @param string $pathPrefix
     */
    public function setPathPrefix(string $pathPrefix): void
    {
        $this->pathPrefix = $pathPrefix;
    }


}
