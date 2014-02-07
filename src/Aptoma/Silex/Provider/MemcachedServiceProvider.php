<?php


namespace Aptoma\Silex\Provider;

use Doctrine\Common\Cache\MemcachedCache;
use Silex\Application;
use Silex\ServiceProviderInterface;

class MemcachedServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['memcached'] = $app->share(
            function () use ($app) {
                $memcached = new \Memcached($app['memcached.identifier']);
                $memcached->setOption(\Memcached::OPT_COMPRESSION, false);
                $memcached->setOption(\Memcached::OPT_PREFIX_KEY, $app['memcached.prefix']);

                $serversToAdd = array_udiff(
                    $app['memcached.servers'],
                    $memcached->getServerList(),
                    function ($a, $b) {
                        return ($a['host'] == $b['host'] && $a['port'] == $b['port']) ? 0 : 1;
                    }
                );
                if (count($serversToAdd)) {
                    $memcached->addServers($serversToAdd);
                }

                return $memcached;
            }
        );

        $app['cache'] = $app->share(
            function () use ($app) {
                if (!class_exists('\Doctrine\Common\Cache\MemcachedCache')) {
                    throw new \Exception('You need to include doctrine/common in order to use the cache service');
                }
                $cache = new MemcachedCache();
                $cache->setMemcached($app['memcached']);

                return $cache;
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
