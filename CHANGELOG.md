# Change log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [[*next-version*]] -YYYY-MM-DD
### Fixed
- Missing return types causing warnings with PHP 8.1 and higher.
- Static check issues.

### Added
- Service tagging capability (#28).

## [0.1.4] -2021-10-06
Stable release.

## [0.1.4-alpha2] - 2021-03-10
### Fixed
- Using newer interop interfaces fixes bug with new `psr/container` (#22).
- Many errors reported by Psalm, making code more resilient (#22).

## [0.1.4-alpha1] - 2021-02-02
### Added
- PHP 8 support!
- More tests, checks, QoL improvements, etc.

## [0.1.3-alpha2] - 2020-10-23
### Added
- `NoOpContainer` (#16).

## [0.1.3-alpha1] - 2020-10-22
### Added
- `FlashContainer` (#15).

## [0.1.2] - 2020-10-22
Stable release.

## [0.1.2-alpha1] - 2020-10-13
### Added
- `SimpleCacheContainer`.

## [0.1.1] - 2020-10-13
Stable release

## [0.1.1-alpha1] - 2020-09-14
### Fixed
- Missing parameter type in `MaskingContainer`'s constructor (#11).

## [0.1.0-alpha1] - 2020-09-14
Initial release.
