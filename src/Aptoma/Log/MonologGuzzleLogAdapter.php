<?php


namespace Aptoma\Log;

use Guzzle\Http\Message\Request;
use Guzzle\Http\Message\Response;
use Guzzle\Log\AbstractLogAdapter;
use Monolog\Logger;

/**
 * MonologGuzzleLogAdapter modifies the default adapter to suit our needs.
 */
class MonologGuzzleLogAdapter extends AbstractLogAdapter
{
    /** @var  Logger */
    protected $log;

    public function __construct(Logger $logObject)
    {
        $this->log = $logObject;
    }

    public function log($message, $priority = LOG_INFO, $extras = null)
    {
        /** @var Response $response */
        $response = $extras['response'];
        $priority = $this->getPriorityFromResponse($response);
        $context = $this->getContextFromResponse($response);

        $this->log->addRecord($priority, $message, $context);

        if ($response->isError()) {
            /** @var Request $request */
            $request = $extras['request'];
            $this->log->addRecord(
                $response->isClientError() ? Logger::WARNING : Logger::ERROR,
                sprintf(
                    'Request failed with code %s: %s %s',
                    $response->getStatusCode(),
                    $request->getMethod(),
                    $request->getUrl()
                ),
                $context
            );
        }
    }

    private function getPriorityFromResponse(Response $response)
    {
        if ($response->isServerError() || $response->getInfo('total_time') > 5) {
            return Logger::ERROR;
        }

        if ($response->isClientError() || $response->getInfo('total_time') > 1) {
            return Logger::WARNING;
        }

        return Logger::INFO;
    }

    private function getContextFromResponse(Response $response)
    {
        $extraFields = array();

        $headersToLookFor = array('x-served-by', 'x-backend', 'x-location', 'x-varnish');

        foreach ($headersToLookFor as $headerName) {
            if ($response->hasHeader($headerName)) {
                $extraFields[$headerName] = $response->getHeader($headerName, true);
            }
        }

        return $extraFields;
    }
}
