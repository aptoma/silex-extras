<?php

namespace Aptoma\Cache;

/**
 * Aptoma\Cache\PredisCache extends the Doctrine Predis cache to add serialization
 */
class PredisCache extends \Doctrine\Common\Cache\PredisCache
{
    protected function doFetch($id)
    {
        return unserialize(parent::doFetch($id));
    }

    protected function doSave($id, $data, $lifeTime = 0)
    {
        return parent::doSave($id, serialize($data), $lifeTime);
    }
}
