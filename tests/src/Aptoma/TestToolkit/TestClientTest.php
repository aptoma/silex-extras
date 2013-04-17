<?php


namespace Aptoma\TestToolkit;

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
                $this->equalTo(array()),
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
                $this->equalTo(array()),
                $this->equalTo(json_encode(array('foo' => 'bar')))
            );

        $client->putJson('/url', array('foo' => 'bar'));
    }

    private function getMockTestClient()
    {
        return $this->getMock('\Aptoma\TestToolkit\TestClient', array('request'), array($this->app));
    }
}
