# Changelog

## [2.1.0] - 2026-03-18

### Added

- Allow installation alongside Laravel 13 by relaxing `illuminate/*` constraints to include `^13`.
- Update documentation to mention Laravel 13 support.

## [2.0.0] - 2026-02-03

### Changed

- Require PHP ^8.1 and support Laravel 10-12.
- Update dependencies: allow PHPUnit ^10.5 || ^11.0, Mockery ^1.6, Guzzle ^7.5.
- Enforce strict notification contract: notifications must implement `toSms($notifiable)`.

### Fixed

- Fix reading the `charset` option from configuration.
