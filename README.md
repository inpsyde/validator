# Inpsyde Validator

This package provides a collection of validators for WordPress. 

## Contents

* [Installation](#installation)
* [Usage](#usage)
   * [ArrayValue](#arrayvalue)
   * [Between](#between)
   * [Date](#Date)
   * [GreaterThan](#greaterthan)
   * [InArray](#inarray)
   * [LessThan](#lessthan)
   * [NotEmpty](#notempty)
   * [RegEx](#regex)
   * [Url](#url)
* [Messages and message templates](#messages-and-message-templates)
* [Options](#options-7)
* [Create your own Validator](#create-your-own-validator)
* [Factory](#factory)
* [Other Notes](#other-notes)
    * [Crafted by Inpsyde](#crafted-by-inpsyde)
    * [Bugs, technical hints or contribute](#bugs-technical-hints-or-contribute)
    * [License](#license)
    * [Changelog](#changelog)


## Installation

```cli
$ composer require --dev [--prefer-dist] inpsyde/validator 
```

## Usage
Each Validator validates a value against a given configuration. Following Validators are available:

### ArrayValue

`Inpsyde\Validator\ArrayValue` allows you to map Validators to an array-key to validate the array-value.

```php
use Inpsyde\Validator as Validator;

$testee = [
    'key1' => 'value1',
    'key2' => ''
];

$validator = new Validator\ArrayValue();
$validator->add_validator( 'key1', new Validator\NotEmpty() );
$validator->add_validator( 'key2', new Validator\NotEmpty() );

$validator->is_valid( $testee ); // FALSE
$messages = $validator->get_error_messages(); // [ "key2" => "This value should not be empty." ]
```

### Between

`Inpsyde\Validator\Between` validates, if a given value is between two values.

#### Messages

* `NOT_BETWEEN`
* `NOT_BETWEEN_STRICT`

#### Options

```
[
    'inclusive' => TRUE,
	'min'       => 0,
	'max'       => PHP_INT_MAX,
]
```

### Date

`Inpsyde\Validator\Date` validates, if a given value is a date.

#### Messages

* `INVALID_TYPE`
* `INVALID_DATE`
* `FALSE_FORMAT`

#### Options

```
[
    'format' => 'd.m.Y',
]
```

### GreaterThan

`Inpsyde\Validator\GreaterThan` validates, if a given value is greater than a minimum border value.

#### Messages

* `NOT_GREATER`
* `NOT_GREATER_INCLUSIVE`

#### Options

```
[
    'inclusive' => FALSE,
    'min'       => 0,
]
```

### InArray

`Inpsyde\Validator\InArray` validates a given value against a haystack of values.

*[!] Note:* This Validator does not validate against multidimensional arrays.

#### Messages

* `NOT_IN_ARRAY`

#### Options

```
[
    'strict'   => TRUE,
    'haystack' => []
]
```

### LessThan

`Inpsyde\Validator\LessThan` validates, if the given value is less than a maximum value.

#### Messages

* `NOT_LESS`
* `NOT_LESS_INCLUSIVE`

#### Options

```php
[
    'inclusive' => FALSE,
    'max'       => 0,
]
```

### NotEmpty

`Inpsyde\Validator\NotEmpty` validates, if a given value is not empty. This Validator checks against `FALSE` OR `empty() && != '0'`. 

#### Messages

* `IS_EMPTY`

### RegEx

`Inpsyde\Validator\RegEx` validates a given value against a defined regular expression.

#### Messages

* `INVALID_TYPE`
* `NOT_MATCH`
* `ERROROUS`

#### Options

```php
[
    'pattern' => ''
]
```

###  Url

`Inpsyde\Validator\Url` validates, if a given value is a valid URL. The Validator uses a regular expression and if successful optionally the DNS-record. 

#### Messages

* `NOT_URL`
* `INVALID_TYPE`
* `INVALID_DNS`
* `NOT_EMPTY`

#### Options

```php
[
    'allowed_protocols' => [ 'http', 'https' ],
    'check_dns'         => FALSE
]
```

## Messages and message templates

Each validator has a set of validation message templates. To customize or translate a message template, you can easily overwrite them in constructor:

```php
use Inpsyde\Validator\Between;

$message_templates = [
    Between::NOT_BETWEEN        => __( 'Your custom message template' ),
    Between::NOT_BETWEEN_STRICT => __( 'Your custom strict message template' )
];
$validator = new Between( [], $message_templates );
```

## Options

Each key of the `$options`-array is reused in message templates as placeholders (`%key%`) to provide more information's in error messages. Additionally the value which is validated is available as `%value%` placeholder.

```php
use Inpsyde\Validator\Between;

$message_templates = [
    Between::NOT_BETWEEN => __( '%value% is not between %min% and %max%.' ),
];
$options = [
    'min' => 1,
    'max' => 3
];
$validator = new Between( $options, $message_templates );
$validator->is_valid( 4 ); // FALSE
$messages = $validator->get_error_messages(); // [ "4 not between 1 and 3" ]
```


## Create your own Validator

### File: `My\Own\Validator\YourValidator.php`

```php
namespace My\Own\Validator;

use Inpsyde\Validator\AbstractValidator;

class YourValidator extends AbstractValidator {

    /**
     * For re-usage when changing the message template outside of the class.
     *
     * @var string
     */
    const INVALID = 'invalid';

    /**
     * Optional: create message templates.
     * @var array
     */
     protected $message_templates = [ self::INVALID => 'The given value %value% is invalid' ];

    /**
     * Optional: create options.
     *
     * @var array
     */
    protected $options = [ 'key' => 'value' ];

    /**
     * {@inheritdoc}
     */
    public function is_valid( $value ) {
        // do something
        if ( FALSE ) {
            $this->set_error_message( self::INVALID, $value );
        
            return FALSE;
        }

        return TRUE;
    }
}
```

### Usage

```php
use My\Own\Validator\YourValidator;


// Optional: overwrite options
$options = [ 'key' => 'new_value' ]

// Optional: overwrite message templates
$message_templates = [ YourValidator::INVALID => 'Another invalid message' ];


$validator = new YourValidator( $options, $message_templates );

// TRUE | FALSE
$valid = $validator->is_valid( 'my value' ); 

// Returns array of messages if is_valid() === FALSE.
$messages = $validator->get_error_messages(); 
```

## Factory

The library comes with a `ValidatorFactory` which allows you to create instances of new validators.

```php
$factory    = new \Inpsyde\Validator\ValidatorFactory();
$validator  = $factory->create( 'Date' ); // returns instance of \Inpsyde\Validator\Date
```

The factory is also able to create instances of external classe, if they implement the `\Inpsyde\Validator\ValidatorInterface`:

```php
$factory    = new \Inpsyde\Validator\ValidatorFactory();
$validator  = $factory->create( My\Own\Validator\YourValidator::class ); // Creates an instance of your own validator.
```


## Other Notes

### Crafted by Inpsyde
    
The team at [Inpsyde](http://www.inpsyde.com) is engineering the Web since 2006.

### Bugs, technical hints or contribute

Please give us feedback, contribute and file technical bugs on [GitHub Repo](https://github.com/inpsyde/Inpsyde-Validator).

### License

Good news, this plugin is free for everyone! Since it's released under the [MIT](https://github.com/inpsyde/Inpsyde-Validator/blob/master/LICENSE), you can use it free of charge on your personal or commercial blog.

### Changelog

See [commits](https://github.com/inpsyde/Inpsyde-Validator/commits/master) or read [short version](https://github.com/inpsyde/Inpsyde-Validator/blob/master/CHANGELOG.md).