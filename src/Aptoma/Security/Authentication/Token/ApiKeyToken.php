<?php

namespace Aptoma\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

/**
 * ApiKeyToken implements an api key token
 *
 * @author Peter Rudolfsen <peter@aptoma.com>
 */
class ApiKeyToken extends AbstractToken
{
    private $apiKey;

    /**
     * Constructor
     *
     * @param string $apikey the users API key
     * @param array $roles an array of optional user roles
     */
    public function __construct($apiKey, array $roles = array())
    {
        parent::__construct($roles);
        $this->apiKey = $apiKey;
        parent::setAuthenticated(count($roles) > 0);
    }

    public function setAuthenticated($isAuthenticated)
    {
        if ($isAuthenticated) {
            throw new \LogicException('Cannot set this token to trusted after instantiation.');
        }

        parent::setAuthenticated(false);
    }

    public function getCredentials()
    {
        return $this->apiKey;
    }
}
