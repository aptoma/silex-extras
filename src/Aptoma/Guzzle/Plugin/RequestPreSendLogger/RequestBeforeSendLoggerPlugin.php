<?php

namespace Aptoma\Guzzle\Plugin\RequestPreSendLogger;

use Guzzle\Common\Event;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RequestBeforeSendLoggerPlugin implements EventSubscriberInterface
{

    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return array(
            'request.before_send' => array('onBeforeRequestSend', 999),
        );
    }

    public function onBeforeRequestSend(Event $event)
    {
        /** @var \Guzzle\Http\Message\Request $guzzleRequest */
        $guzzleRequest = $event['request'];
        $this->logger->info(
            sprintf(
                'Sending %s %s',
                $guzzleRequest->getMethod(),
                $guzzleRequest->getUrl()
            ),
            array('event' => 'request.send')
        );
    }
}
