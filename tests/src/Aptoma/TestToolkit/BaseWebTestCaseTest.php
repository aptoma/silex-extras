<?php


namespace Aptoma\TestToolkit;

use Symfony\Component\HttpFoundation\Request;

class BaseWebTestCaseTest extends BaseWebTestCase
{

    public function testCreateAuthorizedClient()
    {
        $client = $this->createAuthorizedClient();

        $this->assertEquals('username', $client->getServerParameter('PHP_AUTH_USER'));
        $this->assertEquals('password', $client->getServerParameter('PHP_AUTH_PW'));
    }
}
