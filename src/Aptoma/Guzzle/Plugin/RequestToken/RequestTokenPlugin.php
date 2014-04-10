<?php

namespace Aptoma\Guzzle\Plugin\RequestToken;

use Guzzle\Common\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * RequestLoggerPlugin keeps track of number of request and total time spent for requests.
 *
 * It should be registered as a Guzzle plugin, and the writeLog method should called at the
 * end of the request, typically as a finish listener:
 *
 *     $app->finish(array($app['guzzle.request_logger_plugin'], 'writeLog'), Application::LATE_EVENT);
 *
 * If you do any requests in other finish listeners, you should ensure this is the last one to be called.
 */
class RequestTokenPlugin implements EventSubscriberInterface
{

    private $token;
    /** @var RequestStack */
    private $requestStack;

    public function __construct($token, RequestStack $requestStack)
    {
        $this->token = $token;
        $this->requestStack = $requestStack;
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
        if (null !== $remoteToken = $this->getRemoteRequestToken()) {
            $guzzleRequest->addHeader('X-Remote-Request-Token', $remoteToken . ' ' . $this->token);
        } else {
            $guzzleRequest->addHeader('X-Remote-Request-Token', $this->token);
        }
    }

    private function getRemoteRequestToken()
    {
        if (!$this->requestStack->getCurrentRequest()) {
            return null;
        }

        return $this->requestStack->getCurrentRequest()->headers->get('X-Remote-Request-Token');
    }
}
