# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.3.0] - 2026-07-16
### Added

- Added Bancomat Pay

## [3.2.6] - 2026-07-09
### Fixed

- Uncommit composer.lock


## [3.2.5] - 2026-06-18
### Fixed

- Fixed Release package missing vendor directory

## [3.2.4] - 2026-06-15
### Fixed

- Fixed Apple Pay Credentials issue

## [3.2.3] - 2026-06-12
### Changed

- Improved backorder management

## [3.2.2] - 2026-03-23
### Added

- Added Cartes Bancaires as ApplePay supported networks
- Added transaction reference in native table (Admin Order)
- Added additional fields for DSP2

### Changed

- Changed lock type for notifications
- Changed token storage strategy

### Fixed

- Fixed update from previous versions
- Fixed Illicado with Hosted Fields
- Fixed countries & currencies listings
- Fixed Expired notifications for Alma
- Fixed upgrade from previous versions
- Fixed order request for ApplePay + HPP

## [3.2.1] - 2026-03-16
### Added

- Added control on Oney phone numbers
- Added logs on order validation

### Changed

- Updated CB logo

### Fixed

- Fixed status update (added priority)
- Fixed UI on "Pay" button (PS1.7.6 / PS1.7.7)
- Fixed possibility of orders duplicates
- Fixed warning in logs file content

## [3.2.0] - 2026-01-31
### Added

- Added migration interface to migrate data from HiPay Enterprise
- Added new payment methods
- Added "Check Module Health" modal
- Added MO/TO transactions

## [3.1.0] - 2026-01-05
### Added

- Added Advanced Payment Methods (APM)

### Changed

- Changed hashing algorithm management
- Improved refund & capture processes

## [3.0.0] - 2025-11-27
### Added

- First major version for PrestaShop 1.7
