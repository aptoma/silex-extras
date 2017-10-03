<?php

namespace Aptoma\Silex\Provider;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\HandlerStack;
use Pimple\Container;

/**
 * Extends Guzzle service provider for Silex to provide global plugin sipport
 */
class GuzzleServiceProvider
{
    /**
     * Register Guzzle with Silex
     *
     * @param Container $app App container to register with
     */
    public function register(Container $app)
    {
        $app['guzzle.handler_stack'] = function () {
            $stack = HandlerStack::create();

            return $stack;
        };

        $app['guzzle'] = function () {
            $client = new HttpClient([
                'handler' => $app['guzzle.handler_stack']
            ]);

            return $client;
        };
    }
}
