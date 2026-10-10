# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [3.0.0] - 2026-10-10

This release supersedes 2.2.0. It publishes the same library changes under a
major version because they include backward-incompatible API and behavior
changes.

### Added

- Added verified support for PHP 8.5, including PHP 8.5.11.
- Added PHPUnit 12.5 tests for PDO queries, result helpers, transactions,
  migrations, console commands, configuration validation, and error handling.
- Added an optional MySQL integration test controlled by `MYSQL_TEST_*`
  environment variables.
- Added PHPStan 2 static analysis at level 8.
- Added Composer `lint`, `test`, `phpstan`, and `check` scripts.
- Added MIT license metadata and the root `LICENSE` file.
- Added Composer archive exclusions for local and generated files.

### Changed

- Changed the PSR-4 mapping to `WebSK\\` => `src/WebSK/`.
- Updated package metadata and development documentation.
- Made PDO ownership non-nullable in `DB` and simplified lazy MySQL connection
  state handling.
- Added explicit iterable value types for static analysis.
- Changed `DBWrapper::lastInsertId()` to use `null` as its default sequence name,
  matching the underlying PDO API.
- Changed migration files written by `MigrationService::addMigrationQuery()` to
  the documented one-SQL-statement-per-line format.
- Added strict validation and normalization of CLI database configuration.

### Fixed

- Preserved the original `PDOException` when adding request context.
- Added explicit failure handling for `PDO::prepare()`, parameter binding,
  `PDO::lastInsertId()`, `preg_split()`, and CLI input reads.
- Prevented invalid or missing MySQL configuration from reaching the connector.
- Fixed the service factory example in the README.
- Removed unreachable code and completed missing return types.

### Breaking Changes

- `DB::getPdoObj()` and `DB::setPdoObj()` no longer accept or return `null`.
- CLI database configuration now requires string values for `host`, `db_name`,
  `user`, and `password`.
- Migration files produced by `addMigrationQuery()` use line-delimited SQL
  instead of PHP array syntax.

## [2.2.0] - 2026-10-10

### Deprecated

- Superseded by 3.0.0 because this release included backward-incompatible
  changes. Consumers should upgrade to 3.0.0.

## [2.1.0] - 2025-03-25

### Changed

- Removed the `websk/php-config` and `websk/php-utils` runtime dependencies.
- Updated `ulrichsg/getopt-php` from version 3 to version 4.
- Replaced utility assertions with native exceptions.
- Added explicit return types to database and migration APIs.
- Standardized console output on `PHP_EOL`.

## [2.0.1] - 2025-03-25

### Fixed

- Made nullable sequence-name parameters explicit in `DB::lastInsertId()` and
  `DBService::lastInsertId()` for modern PHP compatibility.

## [2.0.0] - 2024-08-22

### Changed

- Raised the minimum supported PHP version from 7.4 to 8.3.
- Updated `websk/php-config` and `websk/php-utils` to version 2.
- Added typed migration command constants.

### Breaking Changes

- PHP 7 is no longer supported; PHP 8.3 or newer is required.

[Unreleased]: https://github.com/websk/php-db/compare/3.0.0...HEAD
[3.0.0]: https://github.com/websk/php-db/compare/2.1.0...3.0.0
[2.2.0]: https://github.com/websk/php-db/compare/2.1.0...2.2.0
[2.1.0]: https://github.com/websk/php-db/compare/2.0.1...2.1.0
[2.0.1]: https://github.com/websk/php-db/compare/2.0.0...2.0.1
[2.0.0]: https://github.com/websk/php-db/compare/1.3.2...2.0.0
