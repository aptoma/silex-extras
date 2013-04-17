<?php


namespace Aptoma\TestToolkit;

use Symfony\Component\HttpFoundation\Request;

class BaseWebTestCaseTest extends BaseWebTestCase
{

    public function setUp()
    {
        $this->pathToAppBootstrap = __DIR__ . '/mocks/app.php';
        parent::setUp();
    }

    public function testCreateApplication()
    {
        $app = $this->createApplication();
        $this->assertInstanceOf('Mock\Application', $app);
    }

    public function testCreateClient()
    {
        $this->assertInstanceOf('Aptoma\TestToolkit\TestClient', $this->createClient());
    }

    public function testCreateAuthorizedClient()
    {
        $client = $this->createAuthorizedClient(array('test_key' => 'test_value'));

        $this->assertEquals('test_value', $client->getServerParameter('test_key'));
        $this->assertEquals('username', $client->getServerParameter('PHP_AUTH_USER'));
        $this->assertEquals('password', $client->getServerParameter('PHP_AUTH_PW'));
    }
}
