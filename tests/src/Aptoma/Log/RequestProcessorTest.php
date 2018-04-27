<?php

namespace Aptoma\Log;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use PHPUnit\Framework\TestCase;

class RequestProcessorTest extends TestCase
{
    public function testInvoke()
    {
        $app = new Application();
        $request = new Request;
        $request->server->set('REMOTE_ADDR', '127.0.0.1');
        $requestStack = new RequestStack();
        $requestStack->push($request);
        $app['request_stack'] = $requestStack;

        $this->setupSecurityMocks($app);

        $token = new AnonymousToken('key', 'testuser');
        $app['security.token_storage']->setToken($token);

        $processor = new RequestProcessor($app);
        $record = $processor(array());

        $this->assertEquals('127.0.0.1', $record['extra']['clientIp']);
        $this->assertEquals('testuser', $record['extra']['user']);
    }

    public function testInvokeShouldSetEmptyUsernameWhenNoContextIsFound()
    {
        $app = new Application();
        $processor = new RequestProcessor($app);

        $this->setupSecurityMocks($app);

        $record = $processor(array());

        $this->assertEquals('', $record['extra']['user']);
    }

    /**
     * @return Application
     */
    private function setupSecurityMocks(Application $app)
    {
        $authenticationManager = $this->createMock(
            'Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface'
        );
        $accessDecisionManager = $this->createMock(
            'Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface'
        );

        $app['security.token_storage'] = new TokenStorage();
        $app['security.authorization_checker'] = new AuthorizationChecker(
            $app['security.token_storage'],
            $authenticationManager,
            $accessDecisionManager
        );

        return $app;
    }
}
