<?php

namespace Aptoma\Silex\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Predis\Client as RedisClient;

class PredisClientServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['predis.client'] = $app->share(
            function () use ($app) {
                $redisClient = new RedisClient(
                    array(
                        'host' => $app['redis.host'],
                        'port' => $app['redis.port'],
                        'database' => $app['redis.database'],
                    ),
                    array(
                        'prefix' => $app['redis.prefix']
                    )
                );

                return $redisClient;
            }
        );

        $app['redis.host'] = '127.0.0.1';
        $app['redis.port'] = 6379;
        $app['redis.prefix'] = 'prefix::';
        $app['redis.database'] = 0;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function boot(Application $app)
    {
    }
}