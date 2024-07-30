# Changelog

All notable changes to `cerberus` will be documented in this file

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [3.0.4 (2023-07-30)](https://github.com/placetopay-org/cerberus/compare/3.0.3...3.0.4)

### Fixed

- Ignore matches `/index.php` in domain tenant finder. A bad domain was generated with a URL as `{my-tenant}/index.php`.  

## [3.0.3 (2023-04-03)](https://github.com/placetopay-org/cerberus/compare/3.0.2...3.0.3)

### Added

- `eduarguz/shift-php-cs` package.

### Changed

- Update `friendsofphp/php-cs-fixer` package to version 3.0
- Add return type `int` to `TenantAware.execute`

## [3.0.2 (2023-10-11)](https://github.com/placetopay-org/cerberus/compare/3.0.1...3.0.2)

### Changed

- When switching tenants, the `SwitchMailerTask` will not instantiate a new mailer.

## [3.0.1 (2023-10-09)](https://github.com/placetopay-org/cerberus/compare/3.0.0...3.0.1)

### Added

- Run GitHub Actions on push/pull_requests for all branches.

### Changed

- Standard for the CHANGELOG.md file.

### Fixed

- TestsCase tearDown error due to an update in the orchestra/testbench package.

## [3.0.0 (2023-07-28)](https://github.com/placetopay-org/cerberus/compare/2.2.0...3.0.0)

### Changed

- Updates `spatie/laravel-multitenancy` to version 3.0.* 

## [2.2.0 (2023-04-13)](https://github.com/placetopay-org/cerberus/compare/2.1.0...2.2.0)

### Added

- Adds `ResetInstancesTask` to reset the containers when switching a tenant using `multitenancy.forget_instances` key [#24](https://github.com/placetopay-org/cerberus/pull/24)

## [2.1.0 (2023-04-13)](https://github.com/placetopay-org/cerberus/compare/2.0.2...2.1.0)

### Added

- Adds `--no-slashes` option to `tenants:artisan` command. This option will prevent slashes from being added to the command. [#23](https://github.com/placetopay-org/cerberus/pull/23)

## [2.0.2 (2023-01-10)](https://github.com/placetopay-org/cerberus/compare/2.0.1...2.0.2)

### Fixed

- Config merge using array with numeric values. [#21](https://github.com/placetopay-org/cerberus/pull/21)

## [2.0.1 (2022-07-07)](https://github.com/placetopay-org/cerberus/compare/2.0.0...2.0.1)

### Added

- Handle JobsRetryRequested queue event

## [2.0.0 (2022-03-24)](https://github.com/placetopay-org/cerberus/compare/1.8.6...2.0.0)

### Added

- Support for Laravel 9
- Support for PHP 8

### Changed

- upgrade laravel-multitenancy to version ^2.0

### Removed

- Support for PHP7
