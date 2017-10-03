<?php

namespace Aptoma\Silex\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * Symfony Routing component Provider for URL generation.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class UrlGeneratorServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['url_generator'] = function ($app) {
            $app->flush();

            return new UrlGenerator($app['routes'], $app['request_context']);
        };
    }
}
