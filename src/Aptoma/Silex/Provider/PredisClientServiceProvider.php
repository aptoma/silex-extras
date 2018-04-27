<?php

namespace Aptoma\Silex\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Predis\Client as RedisClient;

class PredisClientServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['predis.client'] = function () use ($app) {
            $redisClient = new RedisClient(
                array(
                    'host' => $app['redis.host'],
                    'port' => $app['redis.port'],
                    'database' => $app['redis.database'],
                    'persistent' => $app['redis.persistent'],
                    'timeout' => $app['redis.timeout'],
                ),
                array(
                    'prefix' => $app['redis.prefix']
                )
            );

            return $redisClient;
        };

        $app['redis.host'] = '127.0.0.1';
        $app['redis.port'] = 6379;
        $app['redis.prefix'] = 'prefix::';
        $app['redis.database'] = 0;
        $app['redis.persistent'] = false;
        $app['redis.timeout'] = 5.0;
    }
}
