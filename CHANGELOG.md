# Changelog

All notable changes to `cerberus` will be documented in this file

## [v2.2.1 (2023-07-28)](https://github.com/placetopay-org/cerberus/compare/2.2.0...2.2.1)

- Updates `spatie/laravel-multitenancy` to version 3.0.* 

## [v2.2.0 (2023-04-13)](https://github.com/placetopay-org/cerberus/compare/2.1.0...2.2.0)

- Adds `ResetInstancesTask` to reset the containers when switching a tenant using `multitenancy.forget_instances` key [#24](https://github.com/placetopay-org/cerberus/pull/24)

## [v2.1.0 (2023-04-13)](https://github.com/placetopay-org/cerberus/compare/2.0.2...2.1.0)

- Adds `--no-slashes` option to `tenants:artisan` command. This option will prevent slashes from being added to the command. [#23](https://github.com/placetopay-org/cerberus/pull/23)

## [v2.0.2 (2023-01-10)](https://github.com/placetopay-org/cerberus/compare/2.0.1...2.0.2)

- Fixed config merge using array with numeric values. [#21](https://github.com/placetopay-org/cerberus/pull/21)

## [v2.0.1 (2022-07-07)](https://github.com/placetopay-org/cerberus/compare/2.0.0...2.0.1)

- Adds handle JobsRetryRequested queue event

## [v2.0.0 (2022-03-24)](https://github.com/placetopay-org/cerberus/compare/1.8.6...2.0.0)

- add support for Laravel 9
- add support for PHP 8
- drop support for PHP7
- upgrade laravel-multitenancy to version ^2.0
