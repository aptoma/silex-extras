<?php


namespace Aptoma\Silex\Provider;

use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\Common\Cache\PredisCache;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * CacheServiceProvider exposes a DoctrineCache compatible cache implemenations
 *
 * Depending on the cache type you want to use, you alse need to register the
 * corresponding service provider.
 *
 * You can configure the default cache provided by $app['cache'] by specifying
 * $app['cache.default']. Set the value to the key of your preferred cache.
 */
class CacheServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['cache.memcached'] = $app->share(
            function () use ($app) {
                if (!class_exists('\Doctrine\Common\Cache\MemcachedCache')) {
                    throw new \Exception('You need to include doctrine/common in order to use the cache service');
                }
                $cache = new MemcachedCache();
                $cache->setMemcached($app['memcached']);

                return $cache;
            }
        );

        $app['cache.predis'] = $app->share(
            function () use ($app) {
                if (!class_exists('\Doctrine\Common\Cache\PredisCache')) {
                    throw new \Exception('You need to include doctrine/common in order to use the cache service');
                }
                return new PredisCache($app['predis.client']);
            }
        );

        $app['cache'] = $app->share(
            function () use ($app) {
                if ($app->offsetExists('cache.default')) {
                    return $app[$app['cache.default']];
                }

                return $app['memcached'];
            }
        );
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function boot(Application $app)
    {
    }
}
