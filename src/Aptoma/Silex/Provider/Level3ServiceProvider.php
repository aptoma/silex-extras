<?php

namespace Aptoma\Silex\Provider;

use Aptoma\Ftp\Ftp;
use Aptoma\Service\Level3\Level3Service;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Level3 Service Provider.
 *
 * @author Gunnar Lium <gunnar@aptoma.com>
 */
class Level3ServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['level3'] = $app->share(
            function () use ($app) {
                $ftp = new Ftp(
                    $app['level3.ftp_host'],
                    $app['level3.ftp_username'],
                    $app['level3.ftp_password'],
                    $app['logger']
                );
                return new Level3Service(
                    $ftp,
                    $app['level3.tmp_folder'],
                    $app['level3.public_folder'],
                    $app['level3.public_url'],
                    $app['logger'],
                    $app['level3.options']
                );
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
