<?php

namespace Aptoma\Silex\Provider;

use Aptoma\Log\ExtraContextProcessor;
use Aptoma\Log\RequestProcessor;
use Monolog\Formatter\LogstashFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Silex\Application;
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
        $app['logger'] = $app->share(
            $app->extend(
                'logger',
                function (
                    Logger $logger,
                    \Pimple $app
                ) {
                    if ($app['monolog.logstashfile']) {
                        $logstashHandler = new StreamHandler(
                            $app['monolog.logstashfile'],
                            $app['monolog.level']
                        );
                        $logstashHandler->setFormatter(new LogstashFormatter($app['monolog.name']));
                        $logstashHandler->pushProcessor(
                            new ExtraContextProcessor($this->getLogstashExtraContextFields($app))
                        );

                        $logger->pushHandler($logstashHandler);
                    }

                    $processor = new RequestProcessor($app);
                    $logger->pushProcessor($processor);

                    return $logger;
                }
            )
        );
    }

    /**
     * @param \Silex\Application $app
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function boot(Application $app)
    {
    }

    private function getLogstashExtraContextFields(\Pimple $app)
    {
        $extras = array();
        if ($app->offsetExists('meta.service')) {
            $extras['service'] = $app['meta.service'];
        }
        if ($app->offsetExists('meta.customer')) {
            $extras['customer'] = $app['meta.customer'];
        }
        if ($app->offsetExists('meta.environment')) {
            $extras['customer'] = $app['meta.environment'];
        }

        return $extras;
    }
}
