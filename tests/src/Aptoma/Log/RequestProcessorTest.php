<?php

namespace Aptoma\Log;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\SecurityContext;

class RequestProcessorTest extends \PHPUnit_Framework_TestCase
{
    public function testInvoke()
    {
        $app = new Application();
        $request = new Request;
        $request->server->set('REMOTE_ADDR', '127.0.0.1');
        $requestStack = new RequestStack();
        $requestStack->push($request);
        $app['request_stack'] = $requestStack;

        $token = new AnonymousToken('key', 'testuser');
        $context = $this->getSecurityContextMock();
        $context->setToken($token);

        $app['security'] = $context;

        $processor = new RequestProcessor($app);
        $record = $processor(array());

        $this->assertEquals('127.0.0.1', $record['extra']['clientIp']);
        $this->assertEquals('testuser', $record['extra']['user']);
    }

    public function testInvokeShouldSetEmptyUsernameWhenNoContextIsFound()
    {
        $app = new Application();
        $processor = new RequestProcessor($app);

        $context = $this->getSecurityContextMock();
        $app['security'] = $context;

        $record = $processor(array());

        $this->assertEquals('', $record['extra']['user']);
    }

    /**
     * @return SecurityContext
     */
    private function getSecurityContextMock()
    {
        $context = new SecurityContext(
            $this->getMock('Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface'),
            $this->getMock('Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface')
        );
        return $context;
    }
}
