<?php


namespace Aptoma\TestToolkit;

use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\Request;

class TestClientTest extends BaseWebTestCase
{
    public function testPostJson()
    {
        $client = $this->getMockTestClient();
        $client
            ->expects($this->once())
            ->method('request')
            ->with(
                $this->equalTo('POST'),
                $this->equalTo('/url'),
                $this->equalTo(array()),
                $this->equalTo(array()),
                $this->equalTo(array('CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json')),
                $this->equalTo(json_encode(array('foo' => 'bar')))
            );

        $client->postJson('/url', array('foo' => 'bar'));
    }

    public function testPutJson()
    {
        $client = $this->getMockTestClient();
        $client
            ->expects($this->once())
            ->method('request')
            ->with(
                $this->equalTo('PUT'),
                $this->equalTo('/url'),
                $this->equalTo(array()),
                $this->equalTo(array()),
                $this->equalTo(array('CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json')),
                $this->equalTo(json_encode(array('foo' => 'bar')))
            );

        $client->putJson('/url', array('foo' => 'bar'));
    }

    public function testGetResponse()
    {
        $client = $this->createClient();
        $this->app->get(
            '/',
            function () {
                return 'index';
            }
        );

        $client->request('GET', '/');
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $client->getResponse());
    }

    public function testGetJsonDecodedResponseBody()
    {
        $client = $this->getMock('\Aptoma\TestToolkit\TestClient', array('getResponse'), array($this->app));
        $data = array('foo' => 'bar');
        $response = new Response(json_encode($data), 200);
        $client
            ->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($response));

        $this->assertEquals($data, $client->getJsonDecodedResponseBody());
    }

    private function getMockTestClient()
    {
        return $this->getMock('\Aptoma\TestToolkit\TestClient', array('request'), array($this->app));
    }
}
