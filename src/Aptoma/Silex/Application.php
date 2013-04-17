<?php

namespace Aptoma\Silex;

use App\Twig\Extension\AppExtension;
use Aptoma\JsonErrorHandler;
use Aptoma\Silex\Provider\ExtendedLoggerServiceProvider;
use Guzzle\Log\MessageFormatter;
use Guzzle\Log\MonologLogAdapter;
use Guzzle\Plugin\Log\LogPlugin;
use Silex\Application as BaseApplication;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Symfony\Component\HttpFoundation\Request;

/**
 * Aptoma\Application extends Silex\Application and adds default behavior
 * and enhancements suited to our projects.
 *
 * @author Gunnar Lium <gunnar@aptoma.com>
 *
 * This represents the service container, so it will by definition know about
 * a lot of classes. This is not really an issue for this class.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Application extends BaseApplication
{
    protected $defaultValues = array(
        'timer.threshold_info' => 1000,
        'timer.threshold_warning' => 5000,
    );

    public function __construct(array $values = array())
    {
        $values = array_merge($this->defaultValues, $values);
        parent::__construct($values);

        $app = $this;

        $errorHandler = new JsonErrorHandler($app);
        $app->error(array($errorHandler, 'handle'));

        $app->register(new ServiceControllerServiceProvider());
        $app->register(new UrlGeneratorServiceProvider());

        // Register timer function
        $app->finish(array($app, 'logExecTime'));
    }

    public function logExecTime(Request $request)
    {
        $execTime = round(microtime(true) - $this['timer.start'], 6) * 1000;
        $message = sprintf('Script executed in %sms.', $execTime);
        $context = array(
            'msExecTime' => $execTime,
            'method' => $request->getMethod(),
            'path' => $request->getPathInfo(),
        );
        if ($request->getQueryString()) {
            $context['query'] = $request->getQueryString();
        }
        if ($execTime < $this['timer.threshold_info']) {
            $this['logger']->debug($message, $context);
        } elseif ($execTime < $this['timer.threshold_warning']) {
            $this['logger']->info($message, $context);
        } else {
            $this['logger']->warn($message, $context);
        }
    }

    /**
     * @param Application $app
     * @return void
     */
    protected function registerTwig(Application $app)
    {
        if (!$app->offsetExists('twig.path')) {
            return;
        }
        $app->register(
            new TwigServiceProvider(),
            array(
                'twig.path' => $app['twig.path'],
                'twig.options' => $app['twig.options']
            )
        );

        if (class_exists('\App\Twig\Extension\AppExtension')) {
            $app['twig'] = $app->share(
                $app->extend(
                    'twig',
                    function (\Twig_Environment $twig) use ($app) {
                        $twig->addExtension(new AppExtension($app));

                        return $twig;
                    }
                )
            );
        }
    }

    /**
     * @param Application $app
     * @return void
     */
    protected function registerLogger(Application $app)
    {
        if (!$app->offsetExists('monolog.name')) {
            return;
        }
        $app->register(
            new MonologServiceProvider(),
            array(
                'monolog.name' => $app['monolog.name'],
                'monolog.level' => $app['monolog.level'],
                'monolog.logfile' => $app['monolog.logfile'],
            )
        );
        $this->register(new ExtendedLoggerServiceProvider());
    }
}
