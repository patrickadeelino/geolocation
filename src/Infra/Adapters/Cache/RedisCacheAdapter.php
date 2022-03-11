<?php

namespace Infra\Services\Cache;

use Redis;

class RedisCacheAdapter implements CacheAdapter
{
    public function __construct(private readonly Redis $redisClient)
    {
    }

    public function get(string $key) : ?string
    {
        $cache = $this->redisClient->get($key);

        if (is_string($cache)) {
            return $cache;
        }

        return null;
    }

    public function set(string $key, string $value, int $ttlInSeconds) : void
    {
        $this->redisClient->setex($key, $ttlInSeconds, $value);
    }
}
