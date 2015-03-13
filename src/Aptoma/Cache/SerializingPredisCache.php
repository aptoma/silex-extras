<?php

namespace Aptoma\Cache;

use Doctrine\Common\Cache\PredisCache as DoctrinePredisCache;

/**
 * Aptoma\Cache\SerializingPredisCache extends the Doctrine Predis cache to add serialization
 */
class SerializingPredisCache extends DoctrinePredisCache
{
    protected function doFetch($id)
    {
        $value = parent::doFetch($id);

        if (!is_string($value)) {
            return $value;
        }

        try {
            return unserialize($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    protected function doSave($id, $data, $lifeTime = 0)
    {
        return parent::doSave($id, serialize($data), $lifeTime);
    }
}
