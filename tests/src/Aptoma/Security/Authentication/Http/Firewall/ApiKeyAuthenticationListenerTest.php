<?php

namespace Aptoma\Security\Http\Firewall;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Aptoma\Security\Authentication\Token\ApiKeyToken;

class ApiKeyAuthenticationListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandleShouldAuthenticateToken()
    {
        $token = new ApiKeyToken('key');

        $authenticationManager = 'Symfony\\Component\\Security\\Core\\Authentication\\AuthenticationManagerInterface';
        $authenticationManager = $this->getMock($authenticationManager);
        $authenticationManager->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue($token));

        $securityContext = $this->getMock('Symfony\\Component\\Security\\Core\\SecurityContextInterface');
        $securityContext->expects($this->once())
            ->method('setToken')
            ->with($token);

        $listener = new ApiKeyAuthenticationListener($securityContext, $authenticationManager);
        $listener->handle($this->getGetResponseEvent());
    }

    public function testHandleShouldNullifyTokenOnFailure()
    {
        $authenticationManager = 'Symfony\\Component\\Security\\Core\\Authentication\\AuthenticationManagerInterface';
        $authenticationManager = $this->getMock($authenticationManager);
        $authenticationManager->expects($this->once())
            ->method('authenticate')
            ->will($this->throwException(new AuthenticationException('Authentication failed')));

        $securityContext = $this->getMock('Symfony\\Component\\Security\\Core\\SecurityContextInterface');
        $securityContext->expects($this->once())
            ->method('setToken')
            ->with(null);

         $listener = new ApiKeyAuthenticationListener($securityContext, $authenticationManager);
         $listener->handle($this->getGetResponseEvent());
    }

    private function getGetResponseEvent()
    {
        $request = new Request(array('apikey' => 'key'));

        $event = $this->getMockBuilder('Symfony\\Component\\HttpKernel\\Event\\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));

        return $event;
    }
}
