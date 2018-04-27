<?php


namespace Aptoma\Silex\Provider;

use Doctrine\Common\Cache\MemcachedCache;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class MemcachedServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['memcached'] = function () use ($app) {
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
        };
    }
}
