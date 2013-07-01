<?php

namespace Aptoma\Silex\Provider;

use Aptoma\Ftp\Ftp;
use Aptoma\Service\Level3\Level3Service;
use Aptoma\Storage\FileStorage;
use Silex\Application;
use Silex\ServiceProviderInterface;

class StorageServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['storage.type'] = false;

        $app['storage'] = $app->share(
            function () use ($app) {
                if ($app['storage.type'] === 'level3') {
                    return $app['level3'];
                }

                return new FileStorage($app['storage.dir'], $app['storage.public_url_template'], $app['logger']);
            }
        );
    }

    /**
     * @SuppressWarnings(UnusedFormalParameter)
     */
    public function boot(Application $app)
    {
    }
}
