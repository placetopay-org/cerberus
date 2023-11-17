<?php

namespace Placetopay\Cerberus\Tasks;

class PrefixCacheTask extends \Spatie\Multitenancy\Tasks\PrefixCacheTask
{
    public function __construct(
        protected ?string $storeName = null,
        protected ?string $cacheKeyBase = null
    ) {
        parent::__construct();

        $this->cacheKeyBase = $this->originalPrefix . 'tenant_id_';
    }
}
