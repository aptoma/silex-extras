<?php

namespace Aptoma\Security\Authentication\Token;

use Silex\Application;
use Aptoma\Security\Authentication\Token\ApiKeyToken;

class ApiKeyTokenTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructWithApiKeyShouldNotAuthenticateToken()
    {
        $token = new ApiKeyToken('key');
        $this->assertFalse($token->isAuthenticated());
    }

    public function testConstructWithRolesShouldAuthenticateToken()
    {
        $token = new ApiKeyToken('key', array('ROLE_USER'));
        $this->assertTrue($token->isAuthenticated());
    }

    public function testConstructWithApiKShouldSetCredentials()
    {
        $token = new ApiKeyToken('key');
        $this->assertSame('key', $token->getCredentials());
    }

    /**
     * @expectedException \LogicException
     */
    public function testAuthenticateAfterInstantiationShouldThrowException()
    {
        $token = new ApiKeyToken('key');
        $token->setAuthenticated(true);
    }
}
