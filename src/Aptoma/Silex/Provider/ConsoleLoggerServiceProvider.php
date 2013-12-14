<?php

namespace Aptoma\Silex\Provider;

use Monolog\Logger;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Bridge\Monolog\Formatter\ConsoleFormatter;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class ConsoleLoggerServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['console.output'] = $app->share(
            function () {
                return new ConsoleOutput();
            }
        );

        $app['console.input'] = $app->share(
            function () {
                return new ArgvInput();
            }
        );

        $app['logger.console_format'] = "%start_tag%%level_name%:%end_tag% %message%\n";

        $app['logger'] = $app->share(
            $app->extend(
                'logger',
                function (
                    Logger $logger,
                    \Pimple $app
                ) {
                    $consoleHandler = new ConsoleHandler($app['console.output']);
                    if (!class_exists('Symfony\Bridge\Monolog\Handler\ConsoleHandler')) {
                        throw new \Exception('ConsoleLoggerServiceProvider requires symfony/monolog-bridge ~2.4.');
                    }
                    $consoleHandler->setFormatter(new ConsoleFormatter($app['logger.console_format']));
                    $logger->pushHandler($consoleHandler);

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
}
