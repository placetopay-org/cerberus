# An opinionated multitenancy package for Laravel apps

This package is based on the first version of package `spatie/laravel-multitenancy/`.

Because it is a customization, it requires override steps mentioned below for proper installation.

[More information about the package](https://github.com/spatie/laravel-multitenancy/tree/v1).

## Installation

This package can be installed via composer:

``` bash
composer require "placetopay/cerberus:^1.0"
```

### Publishing the config file

You must publish the config file:

``` bash
php artisan vendor:publish --provider="Placetopay\Cerberus\TenancyServiceProvider" --tag="config"
```

### Publishing the migrate file

``` bash
php artisan vendor:publish --provider="Placetopay\Cerberus\TenancyServiceProvider" --tag="migrations"
```

## Testing

You'll need to create the following 3 local MySql databases to be able to run the test suite:

- `laravel_mt_landlord`
- `laravel_mt_tenant_1`
- `laravel_mt_tenant_2`

You can run the package's tests:

``` bash
composer test
```
