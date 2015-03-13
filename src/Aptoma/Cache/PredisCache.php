<?php

namespace Aptoma\Cache;

use Doctrine\Common\Cache\PredisCache as DoctrinePredisCache;

/**
 * Aptoma\Cache\PredisCache extends the Doctrine Predis cache to add serialization
 */
class PredisCache extends DoctrinePredisCache
{
    protected function doFetch($id)
    {
        $value = parent::doFetch($id);
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
