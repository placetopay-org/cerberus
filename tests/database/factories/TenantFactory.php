<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator;
use Placetopay\Cerberus\Models\Tenant;

$factory->define(Tenant::class, fn (Generator $faker) => [
    'app' => $faker->name,
    'name' => $faker->name,
    'domain' => $faker->unique()->domainName,
]);
