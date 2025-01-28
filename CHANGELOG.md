# Changelog

All notable changes to `code-distortion/fluent-dotenv` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).



## [0.3.4] - 2025-01-29

### Added
- Added support for PHP 8.4



## [0.3.3] - 2023-12-31

### Added
- Updated package tooling



## [0.3.2] - 2023-12-27

### Fixed
- Changed dependency phpcsstandards/php_codesniffer back to squizlabs/php_codesniffer - Thanks to [jrfnl for the update](https://github.com/code-distortion/fluent-dotenv/commit/48d01dde1020869eeceef35ecc65d31500681ed5#r135616263)



## [0.3.1] - 2023-12-18

### Fixed
- Added the ability to load .env files with Windows directory separators (i.e. "\\") in the path - thanks to [Tomas Nev](https://github.com/tmsnvd) for [identifying the problem](https://github.com/tmsnvd/fluent-dotenv/commit/0c8b7b2f9c04903ffc7b562bac28b58eb26468eb)



## [0.3.0] - 2023-12-16

### Changed
- Removed vlusas/phpdotenv from the list of dependencies - the choice of whether to use this or symfony/dotenv is now up to the user



## [0.2.0] - 2023-12-16

### Added
- Added support for PHP 8.3
- Updated GitHub Actions workflow to improve platform coverage
- Warning in the documentation that putenv() (which isn't thread-safe) is used to change the getenv() variables temporarily during the .env loading process, when using symfony/dotenv < 5.1.0
- Added support for symfony/dotenv ^7.0

### Fixed
- Fixed bugs when using symfony/dotenv with old versions of PHP on Windows

### Changed
- Removed usage of putenv() temporarily during the .env loading process from all usage of vlucas/phpdotenv (see just below about the new minimum version requirement)
- Removed usage of putenv() temporarily during the .env loading process from usage of symfony/dotenv 5.1.0+

### Removed
- Removed support for (very old versions of) vlucas/phpdotenv < 1.1 - to ensure putenv() isn't used during the .env loading process



## [0.1.6] - 2022-12-19

### Added
- Added support for PHP 8.2



## [0.1.5] - 2022-12-06

### Fixed
- Updated tests so they run again



## [0.1.4] - 2022-01-03

### Added
- Added support for symfony/dotenv:^6.0
- Updated GitHub Actions workflow to improve platform coverage



## [0.1.3] - 2022-01-01

### Fixed
- Changed dependency list to refer to specific versions of PHP - to prevent installation on platforms with future versions of PHP before being tested



## [0.1.2] - 2021-12-25

### Added
- Added support for PHP 8.1
- Added phpstan ^1.0 to dev dependencies
- Tweaks to documentation



## [0.1.1] - 2021-01-06

### Added
- Added support for PHP 8.0
- PSR12 formatting
- Tweaks to documentation



## [0.1.0] - 2020-07-16

### Added
- Beta release
