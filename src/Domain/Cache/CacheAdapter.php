<?php

namespace Domain\Cache;

interface CacheAdapter
{
    public function get(string $key) : ?string;

    /**
     * @param string $key
     * @param string $value
     * @param int $ttlInSeconds Time to live
     */
    public function set(string $key, string $value, int $ttlInSeconds) : void;
}
