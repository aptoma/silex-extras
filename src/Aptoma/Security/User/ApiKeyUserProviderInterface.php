<?php

namespace Aptoma\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;

interface ApiKeyUserProviderInterface extends UserProviderInterface
{
    /**
     * Load user by an API key
     *
     * @param string $apiKey the user's API key
     * @return Symfony\Component\Security\Core\User\UserInterface
     */
    public function loadUserByApiKey($apiKey);
}
