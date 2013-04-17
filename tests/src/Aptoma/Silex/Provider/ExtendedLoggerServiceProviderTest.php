<?php

namespace Aptoma\Silex\Provider;

use Aptoma\Log\ExtraContextProcessor;
use Monolog\Logger;
use Silex\Application;
use Silex\Provider\MonologServiceProvider;

class ExtendedLoggerServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testLoggerShouldNotAddLogstashHandlerIfLogstashFileIsUnset()
    {
        $app = new Application();
        $app['monolog.logfile'] = false;
        $app->register(new MonologServiceProvider());
        $app->register(new ExtendedLoggerServiceProvider());

        /** @var Logger $logger */
        $logger = $app['logger'];
        $handler = $logger->popHandler();

        $this->assertInstanceOf('Monolog\Formatter\LineFormatter', $handler->getFormatter());
    }

    public function testLoggerShouldAddLogstashHandlerIfLogstashFileIsSet()
    {
        $app = new Application();
        $app['monolog.logfile'] = false;
        $app['monolog.logstashfile'] = 'logstash.log';
        $app->register(new MonologServiceProvider());
        $app->register(new ExtendedLoggerServiceProvider());

        /** @var Logger $logger */
        $logger = $app['logger'];
        $handler = $logger->popHandler();

        $this->assertInstanceOf('Monolog\Formatter\LogstashFormatter', $handler->getFormatter());
    }

    public function testMetaFieldsAreSetIfAvailable()
    {
        $app = new Application();
        $app['monolog.logfile'] = false;
        $app['monolog.logstashfile'] = 'logstash.log';
        $app['meta.service'] = 'foo';
        $app['meta.customer'] = 'bar';
        $app['meta.environment'] = 'test';
        $app->register(new MonologServiceProvider());
        $app->register(new ExtendedLoggerServiceProvider());

        /** @var Logger $logger */
        $logger = $app['logger'];
        $handler = $logger->popHandler();

        /** @var ExtraContextProcessor $processor */
        $processor = $handler->popProcessor();

        $this->assertInstanceOf('Aptoma\Log\ExtraContextProcessor', $processor);

        $record = $processor(array());

        $this->assertEquals(array('service' => 'foo', 'customer' => 'bar', 'environment' => 'test'), $record['extra']);
    }

    public function testCoverBoot()
    {
        $app = new Application();
        $app['monolog.logfile'] = false;
        $app->register(new MonologServiceProvider());
        $app->register(new ExtendedLoggerServiceProvider());

        $app->boot();
    }
}
