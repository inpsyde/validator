# Inpsyde Validator

This package provides a collection of validators for WordPress. 

## Contents

* [Installation](#installation)
* [What it is and how it works](#what-it-is-and-how-it-works)
	* [Simple Validators](#simple-validators)
	* [Composed validators](#composed-validators)
	* [Error codes and input data](#error-codes-and-input-data)
	* [Validators factory](#validators-factory)
	* [Error messages](#error-messages)
	* [Error templates](#error-templates)
		* Code-specific templates
		* Error-specific templates
* [`DataValidator`](#)
	* [Add validators to all items](#)
	* [Add validator to specific items](#)
	* [Customize error message templates](#)
	* [Item keys in error messages](#)
	* [Item key labels for error messages](#)
* [Custom validators](#)
	* [Custom validator example](#custom-validator-example)
	* [Upgrading from version 1.0](#upgrading-from-version-10)
* [Other notes](#other-notes)
	* [Crafted by Inpsyde](#crafted-by-inpsyde)
	* [Bugs, technical hints or contribute](#bugs-technical-hints-or-contribute) 
    * [License](#license)
    * [Changelog](#changelog)
	
  


## Installation

Best served by Composer from Packagist. Package name is `inpsyde/validator`.

----------------

## What it is and how it works

The package provides validator objects that can be used to verify that some given data fulfill specific requirements.
 
Most important method for each validator is `is_valid()` that receives some data and return `true` or `false`, depending
on the provided data meets validator requirements.

We can distinguish between "simple" and "compound" validators. Where the latter are validators that are made combining
simple validators together.

### Simple validators

This is a summary of simple validators provided as of now with the package:

Name | Can be used for | Options | Description
--------- | --------- | --------- | ---------
`Between` | Any data | `min`, `max`,`inclusive` | Verifies that given value is between a maximum and a minimum defined in options. 
`Date`    | String, array, integers and`DateTimeInterface` objects | `format` | Verifies that given data is a valid date according to format defined in options.
`GreaterThan` | Any data | `min`,`inclusive` | Verifies that given value is `>` (or `>=`) option value.
`InArray` | Any data | `haystack`,`strict` | Verifies that given value is present in an haystack defined in options.
`LessThan` | Any data | `max`,`inclusive` | Verifies that given value is `<` (or `<=`) option value.
`NotEmpty` | Any data | --- | Verifies that given value is not empty. (Unlike PHP `empty()` function `0` and `'0'` are not considered empty)
`RegEx` | Strings | `pattern` | Verifies that given string matches a regular expression patter defined in options.
`Url` | Strings | `allowed_protocols`, `check_dns` | Verifies that given string is a valid URL. Check the DNS for the URL host.

All validators are defined in `Inpsyde\Validator` namespace, so for example it is possible to use them like this:

```php
$value = 8;

$between = new Inpsyde\Validator\Between(['min' => 10, 'max' => 20, 'inclusive' => false]);

if ( $between->is_valid($value) ) {
  echo "Value {$value} is between 10 and 20".
} else {
  echo "Value {$value} is not between 10 and 20".
}
```

Other validators can be used in a pretty identical fashion.


### Composed validators

At the moment, there are two composed validators, they are:

Name | Can be used for | Options | Description
--------- | --------- | --------- | --------- |
`Multi` | Any data | `stop_on_failure` | Combine more validators together to check the same value. Will be valid if all child validators are valid.
`DataValidator` | arrays or instances of `Traversable` | --- | Validate a collection of data, each child validator is assigned to a different part of the data, assigned by key

**`DataValidator`** is the more powerful validator of the package, because it is the only validator implementing
`ErrorLoggerAwareValidatorInterface` interface that make possible to obtain error messages for validated data ia a very simple way.
For this reason usage of this validator is treated separately below.

**`Multi`** is simpler: it just takes a list of validators and use all of them to validate a single value.

For example:

```php
use Inpsyde\Validator;

$custom_between = new Validator\Multi(
	['stop_on_failure' => TRUE ],
	[
		new Validator\GreaterThan(['min' => 10, inclusive' => true]),
		new Validator\LessThan(['max' => 20, inclusive' => false]),
	]
);
```

The first constructor argument, just like for all the "simple" validators, is an array of options, the second is an
array of validators.

By default all validators are called for the given value when `is_valid()` is called, but setting the option `stop_on_failure`
to TRUE, the validator stop to perform validation when the first failing validator is reached.

An alternative and less verbose way to build a `Multi` validator instance is to use the static method `with_validators()` that accepts
a variadic number of validators objects:


```php
use Inpsyde\Validator;

$custom_between = Validator\Multi::with_validators(
	new Validator\GreaterThan(['min' => 10, inclusive' => true]),
	new Validator\LessThan(['max' => 20, inclusive' => false]),
);
```

When constructed like this, the `stop_on_failure` options is set to its default, that is `false`, but can be set to
`true` by calling `stop_on_failure()` method on obtained instance.

```php
use Inpsyde\Validator;

$custom_between = Validator\Multi::with_validators(...$validators)->stop_on_failure();
```

### Error codes and input data

In the example above the error message in case of failure is hardcoded. However, some validators may fail for different
reasons. For example, `RegEx` validator may fail because the input provided is not a string, because the patter is not valid
or just because the given value does not match the provided valid pattern.

This is why all validators came with two additional methods (alongside `is_valid()`):

* `get_error_code()`
* `get_input_data()`

**`get_error_code()`** returns a code that identifies tha kind of error that made the validator fail.

All error codes are available as class constants of the `Inpsyde\Validator\Error\ErrorLoggerInterface`. 

For example, in the example above `$between->get_error_code()` had returned a `ErrorLoggerInterface::NOT_BETWEEN_STRICT`
error, but if the option `inclusive` was `true`, the returned error would be `ErrorLoggerInterface::NOT_BETWEEN`.

**`get_input_data()`** returns an array with information on

* the option the validator
* the value validated

For example, in the example above `$between->get_input_data()` had returned:

```
[
	'min'   => 10,
	'max'   => 20,
	'value' => 8,
]
```

### Validators factory

The package ships with a validator factory class that can be used to build validator instance starting from some
configuration values.

This is useful when more validators have to built in bulk form configuration files or for lazy instantiation.

The factory has just one method `create()` that accepts a validator identifier as string and an ptional array of options.

Usage example:

```php
$configuration = [
	'between'   => ['min' => 10, 'max' => 20],
	'not-empty' => [],
	'in_array'  => ['haystack' => ['a', 'b', 'c']]
];

$factory = new Inpsyde\Validator\ValidatorFactory();

$validators = [];

foreach($configuration as $identifier => $options) {

	$validators[] = $factory->create( $identifier, $options);
}
```

To construct shipped validators, it is also possible to use as identifier a class name without namespace, like: 

```php
$configuration = [
	'Between'  => ['min' => 10, 'max' => 20],
	'NotEmpty' => [],
	'InArray'  => ['haystack' => ['a', 'b', 'c']]
];
```

For any custom validator that implements validator interfaces, it is possible to pass the fully qualified name of the class
to obtain a constructed instance.

### Error messages

This package comes with objects dedicated to get error messages when validators fails.

They are:

* `Inpsyde\Validator\Error\ErrorLogger`
* `Inpsyde\Validator\Error\WordPressErrorLogger`

The two loggers works in the same way, however `WordPressErrorLogger` has support for translation via WordPress translation
features.

There are two step involved in showing errors on these objects:

1. *Log* the error(s)
2. *Get* the array of logged errors

The code looks like this:

```php
use Inpsyde\Validator;

$between = new Validator\Between(['min' => 10, 'max' => 20, 'inclusive' => false]);

if ( ! $between->is_valid() ) {

	$logger = new Validator\Error\WordPressErrorLogger();
	$logger->log_error( $between->get_error_code(), $between->get_input_data() );
	
	foreach( $logger->get_error_messages() as $error ) {
		echo "<p>{$error}</p>"
	}
}
```

It might seems it requires too much work, however when validating data with `DataValidator` (see below) most of the code
above is not necessary.

### Error templates

When using error loggers, the error messages are created using "templates": message strings that contain placeholders for
values.

Every error code available as constant of `ErrorLoggerInterface` as a related template. For example, for the code
`ErrorLoggerInterface::NOT_BETWEEN` the related template is:

```
'The input <code>%value%</code> is not between <code>%min%</code> and <code>%max%</code>, inclusively.'
```

Where `%value%`, `%min%` and `%max%` are placeholder that are replaced with data passed via `get_input_data()` when the
error is logged.

Error templates can be customized in 2 different ways:

1. replacing the template used for specific codes in the logger
2. passing a specific template when logging an error


#### Code-specific templates

Error loggers comes with a method: `use_error_template()` that can be used to set a custom error template for a given
error code.

For example:

```php
use Inpsyde\Validator\Error;

$logger = new Error\WordPressErrorLogger();
$logger->use_error_template(Error\ErrorLoggerInterface::NOT_BETWEEN, 'Hey, the value %value% is not ok.' );
```

Doing like this, all the errors for `Error\ErrorLoggerInterface::NOT_BETWEEN` will use the given template, unless an
error-specific template is provided when logging the error.

Instead of using `use_error_template()` that replaces error templates one by one, it is possible to replace more
templates at once passing an array of templates, where array keys are the error codes:

```php
use Inpsyde\Validator\Error;

$custom_templates = [
	Error\ErrorLoggerInterface::NOT_BETWEEN        => 'Hey, the value %value% is not ok.',
	Error\ErrorLoggerInterface::NOT_BETWEEN_STRICT => 'Hey, the value %value% is not ok. Really.' 
];

$logger = new Error\WordPressErrorLogger( $custom_templates );
```

#### Error-specific templates

The method `log_error()` accepts a third argument to pass a specific template that will me used for that error only:

```php
use Inpsyde\Validator\Error;

$logger = new Error\WordPressErrorLogger();

$logger->log_error(
	$validator->get_error_code(),
	$validator->get_input_data(),
	'%value% is wrong, try again.'
);
```

When used like this, the custom template does not affect all the other messages for same code, but only the error
being logged.


----------------


## `DataValidator`

`DataValidator` is the more powerful validator in the package. Beside to collect more validators, it also provides more
methods than other validators, including `get_error_messages()` that returns an array of all errors occurred while 
validating given data.

"Child" validators added to `DataValidator` can be used to validate:

- **all** items of the data to validate
- **specific** items identified by their "key"

### Add validators to *all* items

`DataValidator` has two methods to add validators to all the items of the data to validate, they are:

- `add_validator()`
- `add_validator_with_message()`

The first just accept a validator instance, the second also accepts a custom message template that will be used to build
the error message when this validator fail.

Example:

```php
use Inpsyde\Validator;

$validator = new Validator\DataValidator();

$validator
	->add_validator_with_message(new Validator\NotEmpty(), 'The given value must not be empty.')
	->add_validator(new Validator\Url(['check_dns' => true ]));
	
$validator->is_valid([
	'http://www.example.com',
	'http://example.com',
	'this-will-fail'
]);
```

Each element of the array passed to `is_valid()` will be validate against both the validator added.

It is possible to setup `DataValidator` to validate each element of the given data using different validators.

In the example above, note how both `add_validator_with_message()` and `add_validator` implements "fluent interface" 
allowing to chain call to them by returning an instance of validator.


### Add validator to *specific* items

There's one method that allows to add validators to specific element of the given data, it is `add_validator_by_key()`.

It takes three arguments: an instance of validator, a key used to identify the data element, and optionally an error
message template to use for the validator.

Example:

```php
use Inpsyde\Validator;

$validator = new Validator\DataValidator();

$validator
	->add_validator_by_key(new Validator\NotEmpty(), 'name', 'Name cannot be empty.')
	->add_validator_by_key(new Validator\Url(), 'homepage', 'Homepage must be a valid URL.')
	
$valid = $validator->is_valid([
	'name'     => 'Inpsyde',
	'homepage' => 'http://www.inpsyde.com',
]);

if (! $valid) {
	foreach( $validator->get_error_messages() as $error ) {
		echo "<p>{$error}</p>"
    }
}
```

As shown above, `DataValidator` is the only validator that supports `get_error_messages()` to obtain an array of all
error occurred.


### Customize error message templates

By using `add_validator_by_key()` and `add_validator_with_message()` it is possible to customize the error template at
validator level, however, `DataValidator` constructor optionally takes as first argument an instance of error logger
that will be used to build all messages.

So, it is possible to create an error logger instance with custom error messages (as shown above) and pass it to
`DataValidator` constructor:

```php
use Inpsyde\Validator;

$custom_templates = [
	Validator\Error\ErrorLoggerInterface::NOT_BETWEEN        => 'Hey, the value %value% is not ok.',
	Validator\Error\ErrorLoggerInterface::NOT_BETWEEN_STRICT => 'Hey, the value %value% is not ok. Really.' 
];

$logger = new Validator\Error\WordPressErrorLogger( $custom_templates );

$validator = new DataValidator( $logger );
```

### Item keys in error messages

When using `DataValidator`, there is an additional placeholder:`'%key%'`, that will be replaced with the item key of the 
value that caused the error.

By default error messages have no `'%key%'` placeholder, so the string `"<code>%key%</code>: "` is prepended to default message.

For example the default message template `"The input <code>%value%</code> is not a valid URL."` becomes: 
"<code>%key%</code>: The input <code>%value%</code> is not a valid URL."`.

This only happens when **no** custom error template is provided using `add_validator_by_key()` or `add_validator_with_message()`,
when that happens nothing is prepended, but if the custom template contain the `'%key%'` placeholder it will be replaced as well.

### Item key labels for error messages

Sometimes it might be desirable that the error message does not contain the item key, but a label. For example, if the validator
is used for data coming from an HTML form, would be nice if the error message would contain the input label, and not input name.

However, the input name (that will be the key in the submitted data array) is also needed to let `DataValidator` identify
which validator apply to each field.

For this reason, both `add_validator_by_key()` and `add_validator_with_message()` support a special syntax for their second
argument`$key`: an array of two element, with keys `'key'` and `'label'`.

Example:

```php
use Inpsyde\Validator;

$validator = new DataValidator();

$validator->add_validator_by_key(
	new Validator\NotEmpty(),
	[ 'key' => 'username', 'label' => __( 'User name', , 'txtdomain' ) ],
	sprintf( __( '%s must not be empty.', 'txtdomain' ), %key% )
);

if ( ! $validator->is_valid( [ 'username' => '' ] ) ) {
	$messages = $validator->get_error_messages();
}
```

In the example above, because `username` key has an empty value, the error message built will be the **translated version** of

> User name must not be empty.

Note how the label is used to replace the placeholder instead of the key.


----------------


## Custom validators

It is possible to create custom validators to be used with the package.

Custom validators should implements the interface `ExtendedValidatorInterface` which contains the following methods:

* `get_error_code()`
* `get_input_data()`
* `get_error_messages()` (deprecated)
* `is_valid()`

The package ships with 2 traits that can be used to implement the first 3 methods, leaving only `is_valid()` to implementers.

Particularly consider `GetErrorMessagesTrait` that contains implementation for the deprecated `get_error_messages()`
(see "*Upgrading from version 1.0*" below for more info).

### Custom validator example

A trivial custom validator could be something like this:

```php
namespace MyPlugin\Validator;

use Inpsyde\Validator\ExtendedValidatorInterface;
use Inpsyde\Validator\GetErrorMessagesTrait;
use Inpsyde\Validator\ValidatorDataGetterTrait;
use Inpsyde\Validator\Error\ErrorLoggerInterface;

class YesNo implements ExtendedValidatorInterface {

	const ERROR_CODE = 'not_yes_no';

	use GetErrorMessagesTrait;
	use ValidatorDataGetterTrait;
	
	public function is_valid( $value ) {
	
		/** @see ValidatorDataGetterTrait */
		$this->input_data[ 'value' => $value]; 
		
		if ( ! is_string( $value ) ) {
			// this is a default error
			$this->error_code = ErrorLoggerInterface::INVALID_TYPE_NON_STRING;
			
			return false;
		}
			
		if ( ! in_array( strtolower( $value ), ['yes', 'no'], true ) ) {
		    // custom error
			$this->error_code = self::ERROR_CODE;
			
			return false;
		}
		
		return true;
	}
 
}
```

The validator emit two error codes, one is a default one, the other is custom.

If the validator is intended to be used with `DataValidator` it is necessary to add the custom code to the error logger,
something like:

```php
namespace MyPlugin;

use Inpsyde\Validator\DataValidator;
use Inpsyde\Validator\Error\WordPressErrorLogger;

$yes_no_message = sprintf(
	__( 'Accepted values are only "yes" and no. %s was given.', 'txtdmn' ),
	'<code>%value%</code>'
);

$logger = new WordPressErrorLogger([
	Validator\YesNo::ERROR_CODE => $message
]);

$validator = new DataValidator( $logger );

$validator->add_validator_by_key( new Validator\YesNo(), 'accepted' );
```

### Upgrading from version 1.0

* The interface `ExtendedValidatorInterface` that contains `get_error_code()` and `get_input_data()`, was introduced in
  version 1.1 of the package, version 1.0 used `ValidatorInterface`.
* `get_error_messages()` is deprecated from version 1.1
* The whole reason for `ExtendedValidatorInterface` existence is maintain backward compatibility with any custom validator
  built for version 1.0 and extending `ValidatorInterface` (we could not add methods to it without breaking compatibility).

For reasons above starting from version **2.0**:

* `get_error_messages()` will be removed
* `get_error_code()` and `get_input_data()` will be added to `ValidatorInterface`
* `ExtendedValidatorInterface` will so become empty, and will just extend `ValidatorInterface`. It will be maintained for
  compatibility with custom validators written for 1.1+, but will be deprecated and removed from version `3.0`.


----------------


## Other notes

### Crafted by Inpsyde
    
The team at [Inpsyde](http://www.inpsyde.com) is engineering the Web since 2006.

### Bugs, technical hints or contribute

Please give us feedback, contribute and file technical bugs on [GitHub Repo](https://github.com/inpsyde/Inpsyde-Validator).

### License

Good news, this plugin is free for everyone! Since it's released under the [MIT](https://github.com/inpsyde/Inpsyde-Validator/blob/master/LICENSE), you can use it free of charge on your personal or commercial blog.

### Changelog

See [commits](https://github.com/inpsyde/Inpsyde-Validator/commits/master) or read [short version](https://github.com/inpsyde/Inpsyde-Validator/blob/master/CHANGELOG.md).
