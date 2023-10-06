# Changelog

All notable changes to `cerberus` will be documented in this file

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.0.3 (2023-10-06)](https://github.com/placetopay-org/cerberus/pull/27)

### Added

- Run GitHub Actions on push/pull_requests for all branches.

### Fixed

- TestsCase tearDown error due to an update in the orchestra/testbench package.
- Standard for the CHANGELOG.md file.

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
