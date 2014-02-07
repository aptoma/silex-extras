<?php

namespace Aptoma\Guzzle\Plugin\HttpCallInterceptor;

use Aptoma\Guzzle\Plugin\HttpCallInterceptor\Exception\HttpCallToBackendException;
use Guzzle\Common\Event;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * HttpCallInterceptorPlugin listens for request.before_send events, and
 * throws an exception if no response is found
 *
 * The use case for this plugin is to detect when a test makes a call to
 * backend, meaning that the MockPlugin is not configured correctly.
 */
class HttpCallInterceptorPlugin implements EventSubscriberInterface
{
    /** @var  LoggerInterface */
    private $logger;

    public function __construct($logger = null)
    {
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return array(
            // Use a number lower than the MockPlugin
            'request.before_send' => array('onRequestBeforeSend', -1000),
        );
    }

    public function onRequestBeforeSend(Event $event)
    {
        /** @var \Guzzle\Http\Message\Request $request */
        $request = $event['request'];
        if ($request->getResponse()) {
            return;
        }

        $message = sprintf('Call to %s was not intercepted by MockPlugin.', $request->getUrl());
        if ($this->logger) {
            $this->logger->critical($message);
        }

        throw new HttpCallToBackendException($message);
    }
}
