<?php

namespace Placetopay\Cerberus\Commands;

use Placetopay\Cerberus\Commands\Concerns\TenantAware;
use Spatie\Multitenancy\Commands\TenantsArtisanCommand as TenantsArtisanParentCommand;

class TenantsArtisanCommand extends TenantsArtisanParentCommand
{
    use TenantAware;
}
