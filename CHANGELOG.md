# Changelog

## 1.1.1

#### Fixed
* Avoid possible function redeclaration in case of multiple autoloaders, thanks @schlessera, see #6

#### Added
* _Nothing_

#### Changed
* _Nothing_

---------------

## 1.1.0

#### Fixed
* CS fixes

#### Changed
* General refactoring
* Decoupled messages from validators
* Deprecated get_error_messages() validators method
* Deprecated ArrayValue in favor of newly added DataValidator
* Error codes constants moved to ErrorLoggerInterface from single validators classes (old constants still available for BC, but usage is deprecated)

#### Added
* Multi validator
* DataValidator validator
* ErrorLoggerInterface, ErrorLogger and WordPressErrorLogger
* Support for messages translation

---------------

## 1.0.0
* Initial Release.
