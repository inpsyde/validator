# Changelog

## 1.2.5

### Fixed

* Fixed typo in `NOT_LESS` where message pointed two times to `%1$s`.

## 1.2.4

### Fixed

* Fix "manual" loading of translation callback into global `$wp_filter` on autoload. Props @kraftner

### Changed

* _Nothing_

### Added

* _Nothing_


## 1.2.3

### Fixed

* Fix typos in `README.md`.

### Changed

* Removed unused property `$options` from classes.

### Added

* _Nothing_

## 1.2.2

### Fixed

* Fix copy-pasta error in `WordPressErrorLogger`.

### Changed

* _Nothing_

### Added

* _Nothing_

## 1.2.1

### Fixed

* Fix wrong arguments passed to `__()` in `WordPressErrorLogger`. 

### Changed

* _Nothing_

### Added

* _Nothing_

## 1.2.0

### Fixed

* Add missing, but referenced error codes in `ErrorLoggerInterface` 

### Changed

* Updated README
* `composer.lock` is removed from VCS

### Added

* Simple validators: `Callback`, `ClassName`, `Email`, `Size`, `Type` and `WpFilter`
* `SecondaryValidatorInterface` and secondary validators: `Negate`, `Bulk` and `Pool`
* `DataValidator::with_validators()` method
* Unit tests for all new classes

-----


## 1.1.1

### Fixed

* Avoid possible function redeclaration in case of multiple autoloaders, thanks @schlessera, see #6

### Added

* _Nothing_

### Changed

* _Nothing_

-----


## 1.1.0

### Fixed

* CS fixes

### Changed

* General refactoring
* Decoupled messages from validators
* Deprecated `get_error_messages()` validators method
* Deprecated `ArrayValue` in favor of newly added `DataValidator`
* Error codes constants moved to `ErrorLoggerInterface` from single validators classes (old constants still available for BC, but usage is deprecated)

### Added

* `Multi` validator
* `DataValidator`
* `ErrorLoggerInterface`, ErrorLogger and WordPressErrorLogger
* Support for messages translation

-----

## 1.0.0

* Initial Release.
