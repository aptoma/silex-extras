<?php

namespace Aptoma\TestToolkit;

use Silex\Application;
use Silex\WebTestCase;

class BaseWebTestCase extends WebTestCase
{

    protected $pathToAppBootstrap;

    public function createApplication()
    {
        if (!($this->pathToAppBootstrap && is_readable($this->pathToAppBootstrap))) {
            $app = new Application();
        } else {
            $app = require $this->pathToAppBootstrap;
        }

        $app['debug'] = false;
        $app['exception_handler']->disable();

        return $app;
    }

    /**
     * Creates a TestClient.
     *
     * @param array $server An array of server parameters
     *
     * @return TestClient A Client instance
     */
    public function createClient(array $server = array())
    {
        return new TestClient($this->app, $server);
    }

    /**
     * Create a client with basic auth credentials.
     *
     * @return TestClient
     */
    protected function createAuthorizedClient()
    {
        return $this->createClient(
            array(
                'PHP_AUTH_USER' => 'username',
                'PHP_AUTH_PW'   => 'password',
            )
        );
    }
}
