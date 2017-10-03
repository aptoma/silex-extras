<?php

namespace Aptoma\Security\Provider;

use Silex\Application;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Aptoma\Security\Authentication\Token\ApiKeyToken;
use Aptoma\Security\Encoder\SaltLessPasswordEncoderInterface;
use PHPUnit\Framework\TestCase;

class ApiKeyAuthenticationProviderTest extends TestCase
{
    /**
     * @expectedException Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testAuthenticateNonExistentUserShouldThrowExeception()
    {
        $userProvider = $this->createMock('Aptoma\\Security\\User\\ApiKeyUserProviderInterface');
        $userProvider->expects($this->once())
            ->method('loadUserByApiKey')
            ->will($this->returnValue(false));

        $encoder = $this->createMock('Aptoma\\Security\\Encoder\\SaltLessPasswordEncoderInterface');
        $encoder->expects($this->once())
            ->method('encodePassword')
            ->will($this->returnValue('anything'));

        $provider = new ApiKeyAuthenticationProvider($userProvider, $encoder);
        $provider->authenticate(new ApiKeyToken('key'));
    }

    public function testAuthenticateShouldReturnTokenWithUser()
    {
        $user = $this->createMock('Symfony\\Component\\Security\\Core\\User\\UserInterface');
        $user->expects($this->once())
            ->method('getRoles')
            ->will($this->returnValue(array()));

        $userProvider = $this->createMock('Aptoma\\Security\\User\\ApiKeyUserProviderInterface');
        $userProvider->expects($this->once())
            ->method('loadUserByApiKey')
            ->will($this->returnValue($user));

        $encoder = $this->createMock('Aptoma\\Security\\Encoder\\SaltLessPasswordEncoderInterface');

        $provider = new ApiKeyAuthenticationProvider($userProvider, $encoder);
        $token = $provider->authenticate(new ApiKeyToken('key'));

        $this->assertInstanceOf('Aptoma\\Security\\Authentication\\Token\\ApiKeyToken', $token);
        $this->assertSame($user, $token->getUser());
    }
}
