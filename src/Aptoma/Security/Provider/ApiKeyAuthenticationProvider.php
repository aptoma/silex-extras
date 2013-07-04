<?php

namespace Aptoma\Security\Provider;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Aptoma\Security\Authentication\Token\ApiKeyToken;
use Aptoma\Security\User\ApiKeyUserProviderInterface;
use Aptoma\Security\Encoder\SaltLessPasswordEncoderInterface;

class ApiKeyAuthenticationProvider implements AuthenticationProviderInterface
{
    /**
     * Encoder used to encode the API key
     *
     * We're using a saltless password encoder.
     * There is no way of looking up the salt since we don't know who the user is
     * The encoder can of course implement a static, common salt for all passwords
     *
     * @var SaltLessPasswordEncoderInterface
     */
    private $encoder;

    /**
     * User provider
     * @var ApiKeyUserProviderInterface
     */
    private $userProvider;

    public function __construct(ApiKeyUserProviderInterface $userProvider, SaltLessPasswordEncoderInterface $encoder)
    {
        $this->userProvider = $userProvider;
        $this->encoder = $encoder;
    }

    /**
     * Authenticate the user based on an API key
     *
     * @param TokenInterface $token
     */
    public function authenticate(TokenInterface $token)
    {
        $user = $this->userProvider->loadUserByApiKey($this->encoder->encodePassword($token->getCredentials()));

        if (!$user || !($user instanceof UserInterface)) {
            throw new AuthenticationException('Bad credentials');
        }

        $token = new ApiKeyToken($token->getCredentials(), $user->getRoles());
        $token->setUser($user);

        return $token;
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof ApiKeyToken;
    }
}
