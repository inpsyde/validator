# Changelog

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

## 1.1.0

### Fixed

* CS issues

### Changed

* General refactoring
* Decoupled messages from validators and introduction of error loggers. Deprecated `get_error_messages()` validator method.
* Deprecated `ArrayValue` in favor of `DataValidator`

### Added

* `Multi` validator
* Support for translations

-----

## 1.0.0
* Initial Release.