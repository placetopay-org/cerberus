<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Placetopay\Cerberus\Models\Tenant;

class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        return [
            'app' => config('multitenancy.identifier'),
            'name' => $this->faker->company(),
            'domain' => $this->faker->unique()->domainName(),
            'config' => [
                'app' => [
                    'env' => 'local',
                    'name' => 'Colombia',
                    'debug' => true,
                    'locale' => 'es',
                    'timezone' => 'America/Bogota',
                ],
            ],
        ];
    }
}
