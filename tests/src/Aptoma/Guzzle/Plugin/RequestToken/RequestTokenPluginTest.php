<?php


namespace Aptoma\Guzzle\Plugin\RequestToken;

use Guzzle\Common\Event;
use Guzzle\Http\Message\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestTokenPluginTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldBeRegisteredOnRequestBeforeSend()
    {
        $token = 'foo';
        $requestStack = new RequestStack();
        $plugin = new RequestTokenPlugin($token, $requestStack);

        $subscribedEvents = $plugin->getSubscribedEvents();
        $this->assertArrayHasKey('request.before_send', $subscribedEvents);
    }

    public function testListenerShouldAddTokenHeader()
    {
        $token = 'foo';
        $requestStack = new RequestStack();
        $plugin = new RequestTokenPlugin($token, $requestStack);

        $guzzleRequest = new Request('GET', 'http://example.com');
        $event = new Event(array('request' => $guzzleRequest));
        $plugin->onBeforeRequestSend($event);

        $this->assertEquals('foo', $guzzleRequest->getHeaders()->get('x-remote-request-token'));
    }

    public function testListenerShouldAppendToRemoteHeadersAddTokenHeader()
    {
        $token = 'foo';
        $requestStack = new RequestStack();
        $plugin = new RequestTokenPlugin($token, $requestStack);
        $httpRequest = new \Symfony\Component\HttpFoundation\Request();
        $httpRequest->headers->set('x-remote-request-token', 'bar');
        $requestStack->push($httpRequest);

        $guzzleRequest = new Request('GET', 'http://example.com');
        $event = new Event(array('request' => $guzzleRequest));
        $plugin->onBeforeRequestSend($event);

        $this->assertEquals('bar foo', $guzzleRequest->getHeaders()->get('x-remote-request-token'));
    }
}
