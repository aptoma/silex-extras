<?php

namespace Aptoma\Silex\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Aptoma\Security\Http\Firewall\ApiKeyAuthenticationListener;
use Aptoma\Security\Provider\ApiKeyAuthenticationProvider;

/**
 * API key security Service Provider.
 *
 * This service provider adds support for authenticating a user through an API key
 *
 * @author Peter Rudolfsen <peter@aptoma.com>
 */
class ApiKeyServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['security.authentication_listener.factory.api_key'] = $app->protect(
            function ($name, $options) use ($app) {
                unset($options); // not in use
                $app['security.authentication_provider.'.$name.'.api_key'] = $app->share(
                    function () use ($app) {
                        return new ApiKeyAuthenticationProvider(
                            $app['api_key.user_provider'],
                            $app['api_key.encoder']
                        );
                    }
                );

                $app['security.authentication_listener.'.$name.'.api_key'] = $app->share(
                    function () use ($app) {
                        return new ApiKeyAuthenticationListener(
                            $app['security'],
                            $app['security.authentication_manager']
                        );
                    }
                );

                return array(
                    'security.authentication_provider.' . $name . '.api_key',
                    'security.authentication_listener.' . $name . '.api_key',
                    null, // the entry point id
                    'pre_auth' // // the position of the listener in the stack
                );
            }
        );
    }

    /**
     * @param \Silex\Application $app
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function boot(Application $app)
    {
    }
}
