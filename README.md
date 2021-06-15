# An opinionated multitenancy package for Laravel apps

This package can make a Laravel app tenant aware. The philosophy of this package is that it should only provide the bare essentials to enable multitenancy.

The package can determine which tenant should be the current tenant for the request. It also allows you to define what should happen when switching the current tenant to another one. It works for multitenancy projects that need to use one or multiple databases.

Before starting with the package, we highly recommend first watching [this talk by Tom Schlick on multitenancy strategies](https://tomschlick.com/2017/07/25/laracon-2017-multi-tenancy-talk/).

The package contains a lot of niceties such as making queued jobs tenant aware, making an artisan command run for each tenant, an easy way to set a connection on a model, and much more.

Are you a visual learner? Then watch [this video](https://spatie.be/videos/laravel-package-training/laravel-multitenancy) that covers how you can use laravel-multitenancy and how it works under the hood.

## Testing

You'll need to create the following 3 local MySql databases to be able to run the test suite:

- `laravel_mt_landlord`
- `laravel_mt_tenant_1`
- `laravel_mt_tenant_2`

You can run the package's tests:

``` bash
composer test
```
