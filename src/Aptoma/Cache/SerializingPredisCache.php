<?php

namespace Aptoma\Cache;

use Predis\ClientInterface;
use Doctrine\Common\Cache\PredisCache as DoctrinePredisCache;

/**
 * Aptoma\Cache\SerializingPredisCache extends the Doctrine Predis cache to add serialization
 */
class SerializingPredisCache extends DoctrinePredisCache
{
    public function __construct(ClientInterface $client)
    {
        parent::__construct($client);

        // Client is private in DoctrinePredisCache, so we have to overload the constructor to get access to it
        $this->client = $client;
    }

    protected function doFetch($id)
    {
        $value = $this->client->get($id);

        if (null === $value) {
            return false;
        }

        if (!is_string($value)) {
            return $value;
        }

        try {
            return unserialize($value);
        } catch (\Exception $e) {
            return $value;
        }
    }
}
