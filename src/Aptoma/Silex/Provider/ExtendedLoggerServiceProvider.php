<?php

namespace Aptoma\Silex\Provider;

use Aptoma\Log\ExtraContextProcessor;
use Aptoma\Log\RequestProcessor;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\LogstashFormatter;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Silex\Application;
use Silex\Provider\MonologServiceProvider;
use Silex\ServiceProviderInterface;

/**
 * Enhanced Logger Service Provider.
 *
 * This service provider extends the default logger to add logstash handling
 * and other common functionality.
 *
 * @author Gunnar Lium <gunnar@aptoma.com>
 */
class ExtendedLoggerServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['monolog.formatter'] = $app->share(
            function () {
                return new LineFormatter(null, 'Y-m-d H:i:s.u');
            }
        );

        $app['monolog.handler'] = function () use ($app) {
            if (!$app['monolog.logfile']) {
                return new NullHandler();
            }
            if (method_exists('Silex\Provider\MonologServiceProvider', 'translateLevel')) {
                $level = MonologServiceProvider::translateLevel($app['monolog.level']);
            } else {
                $level = $app['monolog.level'];
            }
            $streamHandler = new StreamHandler($app['monolog.logfile'], $level);
            $streamHandler->setFormatter($app['monolog.formatter']);

            return $streamHandler;
        };

        $app['logger'] = $app->share(
            $app->extend(
                'logger',
                function (
                    Logger $logger,
                    \Pimple $app
                ) {
                    $logger->pushProcessor($app['logger.request_processor']);
                    $logger->pushProcessor(new PsrLogMessageProcessor());

                    if (!($app->offsetExists('monolog.logstashfile') && $app['monolog.logstashfile'])) {
                        return $logger;
                    }

                    $logstashHandler = new StreamHandler(
                        $app['monolog.logstashfile'],
                        $app['monolog.level']
                    );
                    $logstashHandler->setFormatter(new LogstashFormatter($app['monolog.name']));

                    $extras = array();
                    if ($app->offsetExists('meta.service')) {
                        $extras['service'] = $app['meta.service'];
                    }
                    if ($app->offsetExists('meta.customer')) {
                        $extras['customer'] = $app['meta.customer'];
                    }
                    if ($app->offsetExists('meta.environment')) {
                        $extras['environment'] = $app['meta.environment'];
                    }
                    $logstashHandler->pushProcessor(new ExtraContextProcessor($extras));

                    $logger->pushHandler($logstashHandler);

                    return $logger;
                }
            )
        );

        $app['logger.request_processor'] = $app->share(
            function () use ($app) {
                return new RequestProcessor($app);
            }
        );
    }

    /**
     * @param \Silex\Application $app
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function boot(Application $app)
    {
    }
}
