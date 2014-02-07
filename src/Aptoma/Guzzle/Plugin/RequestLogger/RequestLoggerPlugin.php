<?php

namespace Aptoma\Guzzle\Plugin\RequestLogger;

use Guzzle\Common\Event;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;

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
class RequestLoggerPlugin implements EventSubscriberInterface
{

    /** @var  LoggerInterface */
    private $logger;
    private $requestCount = 0;
    private $totalRequestTime = 0.0;

    public function __construct($logger)
    {
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return array(
            'request.sent' => array('onRequestSent', 999),
        );
    }

    public function onRequestSent(Event $event)
    {
        $this->requestCount++;
        $this->totalRequestTime += $event['response']->getInfo('total_time');
    }

    /**
     * @return int
     */
    public function getRequestCount()
    {
        return $this->requestCount;
    }

    /**
     * @return double
     */
    public function getTotalRequestTime()
    {
        return $this->totalRequestTime;
    }

    public function writeLog(Request $request)
    {
        if ($this->getRequestCount() == 0) {
            return;
        }

        $message = sprintf(
            'Executed %s API calls in %sms',
            $this->getRequestCount(),
            round($this->getTotalRequestTime() * 1000, 1)
        );
        $context = array(
            'requestCount' => $this->getRequestCount(),
            'totalRequestTime' => $this->getTotalRequestTime(),
            'method' => $request->getMethod(),
            'path' => $request->getPathInfo(),
        );
        if ($request->getQueryString()) {
            $context['query'] = $request->getQueryString();
        }
        $this->logger->info($message, $context);
    }
}
