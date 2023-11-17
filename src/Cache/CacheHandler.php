<?php

namespace Placetopay\Cerberus\Cache;

interface CacheHandler
{
    public function clear(string $prefix = ''): void;
}
