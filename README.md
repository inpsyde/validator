# Inpsyde Validator

This package provides a collection of validators for WordPress. 

## Contents

* [Installation](#installation)
* [What it is and how it works](#what-it-is-and-how-it-works)
	* [Simple Validators](#simple-validators)
	* [Secondary Validators](#secondary-validators)
		* `Negate` example
		* `Bulk` example
		* `Pool` example
	* [Compound validators](#compound-validators)
		* `Multi` example
		* `MultiOr` example
	* [Error codes and input data](#error-codes-and-input-data)
	* [Validators factory](#validators-factory)
	* [Error messages](#error-messages)
	* [Error templates](#error-templates)
		* Code-specific templates
		* Error-specific templates
* [`DataValidator`](#datavalidator)
	* [Add validators to all items](#)
	* [Add validator to specific items](#)
	* [Customize error message templates](#)
	* [Item keys in error messages](#)
	* [Item key labels for error messages](#)
* [Custom validators](#custom-validators)
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
 
Most important method for each validator is `is_valid()` that receives some data and returns `true` or `false`, depending
on the provided data meets validator requirements.

We can distinguish among three types of validators:

- "simple"
- "secondary"
- "compound"

**Simple** validators are used to verify single values, according to some specifications.

**Secondary** validators are created taking a simple validator and modifying its behavior. Can be seen as "decorators" for validators.
 
 **Compound** validators are made by combining togheter more validators.

### Simple validators

This is a summary of simple validators provided as of now with the package:

Name | Can be used for | Options | Description
--------- | --------- | --------- | ---------
`Between` | Any data | `min`, `max`,`inclusive` | Verifies given value is between a maximum and a minimum defined in options.
`Callback` | Any data | `callback` | Run give callback passing value to validate. If callback returns a true-ish value, value is considered valid.
`ClassName` | Strings | `autoload` | Check that value is a valid class name string. Trigger autoload by default, but ir  can be prevented by option.
`Date`    | String, array, integers and`DateTimeInterface` objects | `format` | Verifies that given data is a valid date according to format defined in options.
`Email` | Strings | `check_dns` | Check that value is a valid email. Optionally also checks DNS.
`GreaterThan` | Any data | `min`,`inclusive` | Verifies given value is `>` (or `>=`) option value.
`InArray` | Any data | `haystack`,`strict` | Verifies given value is present in an haystack defined in options.
`LessThan` | Any data | `max`,`inclusive` | Verifies given value is `<` (or `<=`) option value.
`NotEmpty` | Any data | --- | Verifies given value is not empty. (Unlike PHP `empty()` function `0` and `'0'` are not considered empty)
`RegEx` | Strings | `pattern` | Verifies given string matches a regular expression pattern defined in options.
`Size` | Any data | `size` | Verifies given data has size defined by option. For strings it means length, arrays and countable objects are counted, numbers cast to integer.
`Type` | Any data | `type` | Verifies that given data is of a specific type. Works with built-in types like "integer", "string" and with class and interface names. Also has 2 special types "numeric" and "traversable"
`Url` | Strings | `allowed_protocols`, `check_dns` | Verifies that given string is a valid URL. Optionally also checks DNS.
`WpFilter` | Any data | `filter` | Calls `apply_filters` with the filter set, passing the value as argument. If callbacks hooked to filter returns a true-ish value, value is considered valid.

All validators are defined in `Inpsyde\Validator` namespace, so it is possible to use them like this:

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


### Secondary validators

At the moment, are available following secondary validators:

Name | Can be used for | Description
--------- | --------- | --------- |
`Bulk` | Traversable data | Takes one validator and applies it to all items of a traversable value. Validate if validator validates *all* the items.
`Negate` | Any data | Takes one validator and negate its result. If given validator validates, the `Negate` validator will fail, and the other way around.
`Pool` | Traversable data | Similar to `Bulk`, it applies a validator to all items of a traversable value. But it validates if the validator validates any of the items.

All secondary validators have a `with_validator()` static method, that can be used as named constructor to obtain an instance.

#### `Negate` example

Here an example on how to use `Negate` to check that given value is _not_ included in a given haystack of values:

```php
$not_in_array = Negate::with_validator( new InArray( [ 'haystack' => [ 'foo', 'bar' ] ] ) );

$not_in_array->is_valid( 'hello' ); // true
$not_in_array->is_valid( 'foo' ); // false
```

#### `Bulk` example

Here an example on how to use `Bulk` to check that given array contains only strings:

```php
$array_of_strings = Bulk::with_validator( new Type( [ 'type' => 'string' ] ) );

$array_of_strings->is_valid( [ 'foo', 'bar' ] ); // true
$array_of_strings->is_valid( [ 'foo', true  ); // false
```

#### `Pool` example

Here an example on how to use `Pool` to check that given array contains at least a `WP_Post` object:

```php
$has_post = Pool::with_validator( new Type( [ 'type' => 'WP_Post' ] ) );

$has_post->is_valid( [ 'foo', new \WP_Post([ 'id' => 1 ]) ] ); // true
$has_post->is_valid( [ 'foo', true  ); // false
```

`Pool` traverse the given value and returns true when first item of the value validates the inner validator.



### Compound validators

At the moment, following compound validators ar available:

Name | Can be used for | Options | Description
--------- | --------- | --------- | --------- |
`Multi` | Any data | `stop_on_failure` | Combine more validators together to check the same value. Will be valid if all child validators are valid. I.e. it combines validators with `AND` login operand.
`MultiOr` | Any data | --- | Combine more validators together to check the same value. Will be valid if any of the child validators is valid. I.e. it combines validators with `OR` login operand.
`DataValidator` | arrays or instances of `Traversable` | --- | Validate a collection of data, each child validator is assigned to a different part of the data, assigned by key

**`DataValidator`** is the more powerful validator of the package, because it is the only validator implementing
`ErrorLoggerAwareValidatorInterface` interface that make possible to obtain error messages for validated data ia a very simple way.
For this reason usage of this validator is treated separately below.

#### `Multi` example

Here an example on how to use `Multi` validator, to check that given value is an array _and_ has two items _and_ bot of
them are strings:

```php
use Inpsyde\Validator;

$two_items_string_array = new Validator\Multi(
	['stop_on_failure' => TRUE ],
	[
		new Validator\Type( [ 'type' => 'array' ] ),
		new Validator\Size( [ 'type' => 2 ] ),
		Validator\Bulk::with_validator( new Validator\Type( [ 'type' => 'string' ] ) ),
	]
);

$two_items_string_array->is_valid( [' foo', 'bar' ] ); // true
$two_items_string_array->is_valid( [ 'foo', 1 ] ); // false
$two_items_string_array->is_valid( [ 'foo', 'bar', 'baz' ] ); // false

```

The first constructor argument is an array of options, just like for all the "simple" validators.
The second argument is an array of validators.

Please note how we used a secondary validator (`Bulk`) as a _child_ validator for `Multi`: this is totally fine, because
simple, secondary and compound validators all implements same interface.

By default all validators are executed for the given value when `is_valid()` is called, but setting the option `stop_on_failure`
to `TRUE`, the validator stops to perform validation when the first failing validator is reached.

An alternative (and less verbose) way to build a `Multi` validator instance is to use the static method `with_validators()`
that accepts a variadic number of validator objects:


```php
use Inpsyde\Validator;

$two_items_string_array = Validator\Multi::with_validators(
	new Validator\Type( [ 'type' => 'array' ] ),
    new Validator\Size( [ 'type' => 2 ] ),
    Validator\Bulk::with_validator( new Validator\Type( [ 'type' => 'string' ] ) ),
);
```

When constructed like this, the `stop_on_failure` options is set to its default, that is `false`, but can be set to
`true` by calling `stop_on_failure()` method on obtained instance.

```php
use Inpsyde\Validator;

$two_items_string_array = Validator\Multi::with_validators(...$validators)->stop_on_failure();
```

#### `MultiOr` example

`MultiOr` is very similar to `Multi`, but the latter combines validator with an `AND` operand, the former with `OR` operand.

In other words, using `Multi` _all_ the inner validators have to validate to make `Multi` validate, on the contrary `MultiOr`
validates if _at least one of_ inner validators validates.

Here an example on how to use `MultiOr` to validate a value to be in the range from 5 to 10 _or_ in the range 50 to 100:

```php
use Inpsyde\Validator;

$custom_range = Validator\MultiOr::with_validators(
	new Validator\Between( [ 'min' => 5, 'max' => 10 ] ),
    new Validator\Between( [ 'min' => 50, 'max' => 100 ] ),
);

$custom_range->is_valid( 7 ) // true
$custom_range->is_valid( 30 ) // false
$custom_range->is_valid( 60 ) // true
```

### Error codes and input data

Some validators may fail for different reasons.

For example, `RegEx` validator may fail because the input provided is not a string, or because the patter is not valid
or just because the given value does not match the provided pattern.

This is why all validators came with two additional methods (alongside `is_valid()`):

* `get_error_code()`
* `get_input_data()`

**`get_error_code()`** returns a code that identifies the kind of error that made the validator fail.

All default error codes are available as interface constants of `Inpsyde\Validator\Error\ErrorLoggerInterface`. 

For example, `Between` validator might return `ErrorLoggerInterface::NOT_BETWEEN_STRICT`
if `inclusive` option is `true`, or `ErrorLoggerInterface::NOT_BETWEEN` if it is `false`.

**`get_input_data()`** returns an array with information on

* the validator options
* the value that was validated

For example, in the example above `Validator\Between::get_input_data()` might return:

```
[
	'min'   => 10,
	'max'   => 20,
	'value' => 8,
]
```

### Validators factory

The package ships with a validator factory class that can be used to build validator instances starting from some
configuration values.

This is useful when more validators have to built in bulk from configuration files or for lazy instantiation.

The factory has just one method `create()` that accepts a validator identifier as string and an optional array of options.

Usage example:

```php
$configuration = [
	'between'   => [ 'min' => 10, 'max' => 20 ],
	'not-empty' => [],
	'in_array'  => [ 'haystack' => [ 'a', 'b', 'c' ] ]
];

$factory = new Inpsyde\Validator\ValidatorFactory();

$validators = [];

foreach($configuration as $identifier => $options) {

	$validators[] = $factory->create( $identifier, $options);
}
```

To construct shipped validators, it is also possible to use as identifier their class name without namespace, like: 

```php
$configuration = [
	'Between'  => [ 'min' => 10, 'max' => 20 ],
	'NotEmpty' => [],
	'InArray'  => [ 'haystack' => [ 'a', 'b', 'c' ] ]
];
```

For any custom validator (see below) that implements validator interfaces, it is possible to pass the fully qualified
name of the class to obtain a constructed instance.

### Error messages

This package comes with objects dedicated to get error messages when validators fail.

They are:

* `Inpsyde\Validator\Error\ErrorLogger`
* `Inpsyde\Validator\Error\WordPressErrorLogger`

The two loggers works in the same way, however `WordPressErrorLogger` has support for translations via WordPress
translation feature.

There are two step involved in showing errors using these objects:

1. *Log* the error(s)
2. *Get* the array of logged errors

The code looks like this:

```php
use Inpsyde\Validator;

$between = new Validator\Between([ 'min' => 10, 'max' => 20, 'inclusive' => false ]);

if ( ! $between->is_valid() ) {

	$logger = new Validator\Error\WordPressErrorLogger();
	$logger->log_error( $between->get_error_code(), $between->get_input_data() );
	
	foreach( $logger->get_error_messages() as $error ) {
		echo "<p>{$error}</p>";
	}
}
```

It might seem it requires too much work, however when validating data with `DataValidator` (see below) most of the code
above is not necessary.

### Error templates

When using error loggers, the error messages are created using "templates": message strings that contain placeholders
for values.

Every error code available as constant of `ErrorLoggerInterface` has a related template.

For example, for the code `ErrorLoggerInterface::NOT_BETWEEN` the related template is:

```
'The input <code>%value%</code> is not between <code>%min%</code> and <code>%max%</code>, inclusively.'
```

Where `%value%`, `%min%` and `%max%` are placeholders that are replaced with data passed via `get_input_data()` when the
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
$logger->use_error_template( Error\ErrorLoggerInterface::NOT_BETWEEN, 'Hey, the value %value% is not ok.' );
```

Doing like this, all the errors for `Error\ErrorLoggerInterface::NOT_BETWEEN` will use the given template, unless an
error-specific template is provided when logging the error.

Instead of using `use_error_template()` that replaces error templates one by one, it is possible to replace more
templates at once passing to logger constructor an array of templates where array keys are the error codes:

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
	$validator->get_error_code(),   // code
	$validator->get_input_data(),   // input data
	'%value% is wrong, try again.', // custom error message template
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

The first just accepts a validator instance, the second also accepts a custom message template that will be used to
build the error message when this validator fail.

Example:

```php
use Inpsyde\Validator;

$validator = new Validator\DataValidator();

$validator
	->add_validator_with_message( new Validator\NotEmpty(), 'The given value must not be empty.' )
	->add_validator( new Validator\Url([ 'check_dns' => true ]) );
	
$validator->is_valid([
	'http://www.example.com',
	'http://example.com',
	'this-will-fail'
]);
```

Each element of the array passed to `is_valid()` will be validated against both the validators added.

In the example above, note how both `add_validator_with_message()` and `add_validator` implements "fluent interface" 
allowing to "chain" calls to them by returning an instance of validator.


### Add validator to *specific* items

`DataValidator` also has one method that allows to add validators to specific element of the given data, it is
`add_validator_by_key()`.

It takes three arguments: an instance of validator, a key used to identify the data element, and optionally an error
message template to use for the validator.

Example:

```php
use Inpsyde\Validator;

$validator = new Validator\DataValidator();

$validator
	->add_validator_by_key( new Validator\NotEmpty(), 'name', 'Name cannot be empty.' )
	->add_validator_by_key( new Validator\Url(), 'homepage', 'Homepage must be a valid URL.' )
	
$valid = $validator->is_valid([
	'name'     => 'Inpsyde',
	'homepage' => 'http://www.inpsyde.com',
]);

if (! $valid) {
	foreach( $validator->get_error_messages() as $error ) {
		echo "<p>{$error}</p>";
    }
}
```

`DataValidator` is the only validator that supports `get_error_messages()` to obtain an array of all
error occurred.


### Customize error message templates

By using `add_validator_by_key()` and `add_validator_with_message()` it is possible to customize the error template at
validator level, however, `DataValidator` constructor optionally takes as first argument an instance of error logger
that will be used to build all messages.

So, it is possible to create an error logger instance with custom error messages (as shown above) and pass it to
`DataValidator` constructor:

```php
use Inpsyde\Validator\Error;
use Inpsyde\Validator\DataValidator;

$custom_templates = [
	Error\ErrorLoggerInterface::NOT_BETWEEN        => 'Hey, the value %value% is not ok.',
	Error\ErrorLoggerInterface::NOT_BETWEEN_STRICT => 'Hey, the value %value% is not ok. Really.' 
];

$logger = new Error\WordPressErrorLogger( $custom_templates );

$validator = new DataValidator( $logger );
```

### Item keys in error messages

When using `DataValidator`, there is an additional placeholder:`'%key%'`, that will be replaced with the item key of the 
value that caused the error.

By default, error messages have no `'%key%'` placeholder, so the string `"<code>%key%</code>: "` is prepended to default message.

For example, the default message template

> The input <code>%value%</code> is not a valid URL.
 
becomes: 

> <code>%key%</code>: The input <code>%value%</code> is not a valid URL.

This only happens when **no** custom error template is provided using `add_validator_by_key()` or `add_validator_with_message()`,
when custom error template is provided, nothing is prepended to it, but if the custom template contains the `'%key%'` placeholder
it will be replaced as well.

### Item key labels for error messages

Sometimes it might be desirable that the error message does not contain the item key, but a label.

For example, if the validator is used to validate data coming from an HTML form, would be nice if the error message would
contain the input label, and not input name.

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
	[ 'key' => 'username', 'label' => __( 'User name', , 'txtdomain' ) ], // key param is an array here
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

Custom validators should implement the interface `ExtendedValidatorInterface` which contains the following methods:

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
		$this->input_data[ 'value' => $value ]; 
		
		if ( ! is_string( $value ) ) {
			// this is a default error
			$this->error_code = ErrorLoggerInterface::INVALID_TYPE_NON_STRING;
			
			return false;
		}
			
		if ( ! in_array( strtolower( $value ), [ 'yes', 'no' ], true ) ) {
		    // custom error
			$this->error_code = self::ERROR_CODE;
			
			return false;
		}
		
		return true;
	}
 
}
```

The validator might emit two error codes in case of error, one of them is a default error code, the other is custom.

If the validator is intended to be used with `DataValidator`, it is necessary to add the custom code to the error logger,
something like:

```php
namespace MyPlugin;

use Inpsyde\Validator\DataValidator;
use Inpsyde\Validator\Error\WordPressErrorLogger;

$yes_no_message = sprintf(
	__( 'Accepted values are only "yes" and "no". "%s" was given.', 'txtdmn' ),
	'%value%'
);

$logger = new WordPressErrorLogger([ Validator\YesNo::ERROR_CODE => $message ]);

$validator = new DataValidator( $logger );

$validator->add_validator_by_key( new Validator\YesNo(), 'accepted' );
```

### Upgrading from version 1.0

* The interface `ExtendedValidatorInterface` that extends `ValidatorInterface` and contains
  `get_error_code()` and `get_input_data()`, was introduced in version 1.1 of the package.
  In version 1.0 validators implemented just `ValidatorInterface`.
* `get_error_messages()` is deprecated from version 1.1
* The whole reason for `ExtendedValidatorInterface` existence is to maintain backward compatibility with any custom validator
  built for version 1.0 and so extending `ValidatorInterface` (we could not add methods to it without breaking compatibility).

For reasons above starting from version **2.0**:

* `get_error_messages()` will be removed
* `get_error_code()` and `get_input_data()` will be added to `ValidatorInterface`
* `ExtendedValidatorInterface` will thus become empty, and will just extend `ValidatorInterface`.
  It will be maintained for backward compatibility with custom validators written for 1.1+, but will be deprecated and
  removed from version `3.0`.


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
