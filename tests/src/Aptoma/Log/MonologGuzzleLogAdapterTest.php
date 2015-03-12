<?php


namespace Aptoma\Log;

use Guzzle\Http\Message\Request;
use Guzzle\Http\Message\Response;
use Monolog\Handler\TestHandler;
use Monolog\Logger;

class MonologGuzzleLogAdapterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider responseTimeDataProvider
     */
    public function testLogShouldSetLogLevelBasedOnResponseTimes($responseTime, $checkLevelMethod)
    {
        $logger = new Logger('test');
        $handler = new TestHandler();
        $logger->pushHandler($handler);
        $adapter = new MonologGuzzleLogAdapter($logger);

        $response = new Response(200);
        $response->setInfo(array('total_time' => $responseTime));
        $extras = array('response' => $response, 'request' => new Request('GET', ''));
        $adapter->log('debug', LOG_DEBUG, $extras);

        $this->assertTrue($handler->{$checkLevelMethod}('debug'));
    }

    public function responseTimeDataProvider()
    {
        return array(
            array(0.1, 'hasInfo'),
            array(2, 'hasWarning'),
            array(6, 'hasError'),
        );
    }

    public function testLogShouldSetLogLevelToErrorIfResponseCodeIndicatesFailure()
    {
        $logger = new Logger('test');
        $handler = new TestHandler();
        $logger->pushHandler($handler);
        $adapter = new MonologGuzzleLogAdapter($logger);

        $response = new Response(500);
        $extras = array('response' => $response, 'request' => new Request('GET', ''));
        $adapter->log('error occured', LOG_DEBUG, $extras);

        $this->assertTrue($handler->hasError('error occured'));
    }

    public function testLogShouldAddContextFromHeaders()
    {
        $logger = new Logger('test');
        $handler = new TestHandler();
        $logger->pushHandler($handler);
        $adapter = new MonologGuzzleLogAdapter($logger);

        $response = new Response(
            200,
            array(
                'x-location' => 'london',
                'x-backend' => 'drlib',
                'x-served-by' => 'varnish-01',
                'x-varnish' => '123456',
                'responseTime' => 0.12,
            )
        );
        $extras = array('response' => $response, 'request' => new Request('GET', ''));
        $adapter->log('debug', LOG_DEBUG, $extras);
        $records = $handler->getRecords();
        unset($records[0]['context']['event']);

        $this->assertEquals(
            array('x-served-by', 'x-backend', 'x-location', 'x-varnish', 'responseTime'),
            array_keys($records[0]['context'])
        );
    }

    public function testLogShouldCreateExtraEntryForFailedRequests()
    {
        $logger = new Logger('test');
        $handler = new TestHandler();
        $logger->pushHandler($handler);
        $adapter = new MonologGuzzleLogAdapter($logger);

        $response = new Response(500);
        $request = new Request('GET', 'http://www.example.com');
        $extras = array('response' => $response, 'request' => $request);
        $adapter->log('error occured', LOG_DEBUG, $extras);
        $records = $handler->getRecords();

        $this->assertEquals('Request failed with code 500: GET http://www.example.com', $records[1]['message']);
    }
}
