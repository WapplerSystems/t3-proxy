<?php

namespace WapplerSystems\Proxy\Plugin;

use WapplerSystems\Proxy\Event\ProxyEvent;
use WapplerSystems\Proxy\Proxy;

abstract class AbstractPlugin
{

    protected Proxy $proxy;

    protected array $settings;

    public function __construct($settings)
    {
        $this->settings = $settings;
    }

    public function onBeforeRequest(ProxyEvent $event)
    {
        // fired right before a request is being sent to a proxy
    }

    public function onHeadersReceived(ProxyEvent $event)
    {
        // fired right after response headers have been fully received - last chance to modify before sending it back to the user
    }

    public function onCurlWrite(ProxyEvent $event)
    {
        // fired as the data is being written piece by piece
    }

    public function onCompleted(ProxyEvent $event)
    {
        // fired after the full response=headers+body has been read - will only be called on "non-streaming" responses
    }

    final public function subscribe($dispatcher)
    {
        $this->proxy = $dispatcher;

        $dispatcher->addListener('request.before_send', function ($event) {
            $this->route('request.before_send', $event);
        });

        $dispatcher->addListener('request.sent', function ($event) {
            $this->route('request.sent', $event);
        });

        $dispatcher->addListener('curl.callback.write', function ($event) {
            $this->route('curl.callback.write', $event);
        });

        $dispatcher->addListener('request.complete', function ($event) {
            $this->route('request.complete', $event);
        });
    }

    // dispatch based on filter
    private function route($event_name, ProxyEvent $event)
    {

        switch ($event_name) {

            case 'request.before_send':
                $this->onBeforeRequest($event);
                break;

            case 'request.sent':
                $this->onHeadersReceived($event);
                break;

            case 'curl.callback.write':
                $this->onCurlWrite($event);
                break;

            case 'request.complete':
                $this->onCompleted($event);
                break;
        }
    }
}

