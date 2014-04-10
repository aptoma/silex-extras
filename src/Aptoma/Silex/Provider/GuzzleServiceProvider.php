<?php

namespace Aptoma\Silex\Provider;

use Aptoma\Guzzle\Plugin\RequestLogger\RequestLoggerPlugin;
use Aptoma\Guzzle\Plugin\RequestPreSendLogger\RequestBeforeSendLoggerPlugin;
use Aptoma\Guzzle\Plugin\RequestToken\RequestTokenPlugin;
use Aptoma\Log\MonologGuzzleLogAdapter;
use Doctrine\Common\Cache\MemcachedCache;
use Guzzle\Cache\DoctrineCacheAdapter;
use Guzzle\GuzzleServiceProvider as BaseGuzzleServiceProvider;
use Guzzle\Log\MessageFormatter;
use Guzzle\Plugin\Cache\CachePlugin;
use Guzzle\Plugin\Cache\DefaultCacheStorage;
use Guzzle\Plugin\Log\LogPlugin;
use Guzzle\Service\Builder\ServiceBuilder;
use Silex\Application;

/**
 * Extends Guzzle service provider for Silex to provide global plugin sipport
 */
class GuzzleServiceProvider extends BaseGuzzleServiceProvider
{
    /**
     * Register Guzzle with Silex
     *
     * @param Application $app Application to register with
     */
    public function register(Application $app)
    {
        parent::register($app);
        $app['guzzle'] = $app->share(
            $app->extend(
                'guzzle',
                function (
                    ServiceBuilder $builder,
                    $app
                ) {
                    foreach ($app['guzzle.plugins'] as $plugin) {
                        $builder->addGlobalPlugin($plugin);
                    }

                    return $builder;
                }
            )
        );

        $app['guzzle.request_logger_plugin'] = $app->share(
            function () use ($app) {
                return new RequestLoggerPlugin($app['logger']);
            }
        );

        $app['guzzle.request_before_send_logger_plugin'] = $app->share(
            function () use ($app) {
                return new RequestBeforeSendLoggerPlugin($app['logger']);
            }
        );

        $app['guzzle.request_token_plugin'] = $app->share(
            function () use ($app) {
                return new RequestTokenPlugin($app['request_token'], $app['request_stack']);
            }
        );

        $app['guzzle.log_plugin'] = $app->share(
            function () use ($app) {
                $logAdapter = new MonologGuzzleLogAdapter($app['logger']);
                $logFormatter = new MessageFormatter('{code} {method} {url} in {total_time}s');
                return new LogPlugin($logAdapter, $logFormatter);
            }
        );

        $app['guzzle.cache_plugin'] = $app->share(
            function () use ($app) {
                $cache = new MemcachedCache();
                $cache->setMemcached($app['memcached']);

                return new CachePlugin(
                    array(
                        'storage' => new DefaultCacheStorage(
                            new DoctrineCacheAdapter($cache),
                            '',
                            $app['cache.default_ttl']
                        )
                    )
                );
            }
        );
    }
}
