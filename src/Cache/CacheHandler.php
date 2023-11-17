<?php

namespace Placetopay\Cerberus\Cache;

interface CacheHandler
{
    /**
     * @return int number of records deleted
     */
    public function clear(string $prefix = ''): int;
}
