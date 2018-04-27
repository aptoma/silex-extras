<?php

namespace Aptoma\Silex\Provider;

use Aptoma\Storage\FileStorage;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class StorageServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['storage.type'] = false;

        $app['storage'] = function () use ($app) {
            return new FileStorage($app['storage.dir'], $app['storage.public_url_template'], $app['logger']);
        };
    }
}
