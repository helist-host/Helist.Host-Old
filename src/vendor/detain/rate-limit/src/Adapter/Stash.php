<?php

namespace Detain\RateLimit\Adapter;

use Detain\RateLimit\Adapter;
use Stash\Invalidation;

/**
 * This could be changed to just require something implmenting PSR6 - i.e. require a \Cache\CacheItemPoolInterface - but
 * Stash seems to require the 'setInvalidationMethod()' to be called on items....
 */
class Stash extends Adapter
{

    /**
     * @var \Stash\Pool
     */
    private $pool;

    public function __construct(\Stash\Pool $pool)
    {
        $this->pool = $pool;
    }

    public function get($key)
    {
        $item = $this->pool->getItem($key);
        $item->setInvalidationMethod(Invalidation::OLD);

        if ($item->isHit()) {
            return $item->get();
        }
        return (float) 0;
    }

    public function set($key, $value, $ttl)
    {
        $item = $this->pool->getItem($key);
        $item->set($value);
        $item->expiresAfter($ttl);
        return $item->save();
    }

    public function exists($key)
    {
        $item = $this->pool->getItem($key);
        $item->setInvalidationMethod(Invalidation::OLD);
        return $item->isHit();
    }

    public function del($key)
    {
        return $this->pool->deleteItem($key);
    }
}
