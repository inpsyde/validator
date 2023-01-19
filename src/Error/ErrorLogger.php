<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the inpsyde-validator package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inpsyde\Validator\Error;

use Inpsyde\Validator\ExtendedValidatorInterface;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package inpsyde-validator
 */
class ErrorLogger implements ErrorLoggerInterface {

	/**
	 * @var string[]
	 */
	private $messages = [ ];

	/**
	 * @var string[]
	 */
	private $errors = [ ];

	/**
	 * @var string
	 */
	private $last_error = '';

	/**
	 * @var string[]
	 */
	private $key_labels = [ ];

	/**
	 * Constructor.
	 *
	 * @param string[] $messages An array of messages to replace default ones.
	 */
	public function __construct( array $messages = [ ] ) {

		$default = [
			self::CUSTOM_ERROR                 => 'Some errors occurred for <code>%value%</code>.',
			self::INVALID_DATE                 => 'The input <code>%value%</code> does not appear to be a valid date.',
			self::INVALID_DATE_FORMAT          => 'The input <code>%value%</code> does not fit the date format <code>%format%</code>.',
			self::INVALID_DNS                  => 'The host for the given input <code>%value%</code> could not be resolved.',
			self::INVALID_TYPE_NON_ARRAY       => 'Invalid type given for <code>%value%</code>. Array expected.',
			self::INVALID_TYPE_NON_COUNTABLE   => 'Invalid type given for <code>%value%</code>. Countable data expected.',
			self::INVALID_TYPE_NON_DATE        => 'Invalid type given for <code>%value%</code>. String, integer, array or DateTime expected.',
			self::INVALID_TYPE_NON_NUMERIC     => 'Invalid type given for <code>%value%</code>. Integer or float expected.',
			self::INVALID_TYPE_NON_SCALAR      => 'Invalid type given for <code>%value%</code>. String, integer or float expected.',
			self::INVALID_TYPE_NON_STRING      => 'Invalid type given for <code>%value%</code>. String expected.',
			self::INVALID_TYPE_NON_TRAVERSABLE => 'Invalid type given for <code>%value%</code>. Array or object implementing Traversable expected.',
			self::INVALID_TYPE_GIVEN           => 'Invalid type given for <code>%value%</code>. <code>%type%</code> expected.',
			self::INVALID_SIZE                 => 'Size for input <code>%value%</code> is not <code>%size%</code>.',
			self::IS_EMPTY                     => 'This value should not be empty.',
			self::MULTIPLE_ERRORS              => 'Some errors occurred for <code>%value%</code>.',
			self::NOT_BETWEEN                  => 'The input <code>%value%</code> is not between <code>%min%</code> and <code>%max%</code>, inclusively.',
			self::NOT_BETWEEN_STRICT           => 'The input <code>%value%</code> is not strictly between <code>%min%</code> and <code>%max%</code>.',
			self::NOT_CLASS_NAME               => 'The input <code>%value%</code> is not a valid class name.',
			self::NOT_EMAIL                    => 'The input <code>%value%</code> is not a valid email address.',
			self::NOT_GREATER                  => 'The input <code>%value%</code> is not greater than <code>%min%</code>.',
			self::NOT_GREATER_INCLUSIVE        => 'The input <code>%value%</code> is not greater or equal than <code>%min%</code>.',
			self::NOT_IN_ARRAY                 => 'The input <code>%value%</code> is not in the haystack: <code>%haystack%</code>.',
			self::NOT_LESS                     => 'The input <code>%value%</code> is not less than <code>%max%</code>.',
			self::NOT_LESS_INCLUSIVE           => 'The input <code>%value%</code> is not less or equal than <code>%max%</code>.',
			self::NOT_NEGATE_VALIDATOR         => '<code>%validator_name%::is_valid()</code> returned true, but was expected to return false.',
			self::NOT_MATCH                    => 'The input does not match against pattern <code>%pattern%</code>.',
			self::NOT_URL                      => 'The input <code>%value%</code> is not a valid URL.',
			self::REGEX_INTERNAL_ERROR         => 'There was an internal error while using the pattern <code>%pattern%</code>.',
			self::WP_FILTER_ERROR              => 'The filter <code>%filter%</code> returned a false value for <code>%value%</code>.',
		];

		$this->messages = array_merge( $default, array_filter( $messages, 'is_string' ) );
	}

	/**
	 * @inheritdoc
	 */
	public function log_error( $code, array $data = [ ], $error_template = NULL ) {

		$this->check_error_code( $code );
		is_null( $error_template )
			? $error_template = $this->messages[ $code ]
			: $this->check_error_template( $error_template );

		$error_message = $this->build_message( $data, $error_template );

		isset( $this->errors[ $code ] ) or $this->errors[ $code ] = [ ];
		$this->errors[ $code ][] = $error_message;
		$this->last_error        = $error_message;

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function log_error_for_key( $key, $code, array $data = [ ], $error_template = NULL ) {

		if ( is_array( $key ) && count( $key ) === 1 && is_string( key( $key ) ) && is_string( reset( $key ) ) ) {
			$key_key                      = key( $key );
			$this->key_labels[ $key_key ] = reset( $key );
			$key                          = $key_key;
		}

		if ( ! is_string( $key ) ) {
			throw new \InvalidArgumentException(
				sprintf( 'Error key must be in a string, %s given.', gettype( $key ) )
			);
		}

		if ( is_null( $error_template ) ) {
			$this->check_error_code( $code );
			$error_template = '<code>%key%</code>: ' . $this->messages[ $code ];
		}

		return $this->log_error( $code, $data, $error_template );
	}

	/**
	 * @inheritdoc
	 */
	public function get_error_messages( $error_code = NULL ) {

		if ( empty( $this->errors ) ) {
			return [ ];
		}

		if ( ! is_null( $error_code ) ) {
			$this->check_error_code( $error_code );

			return isset( $this->errors[ $error_code ] ) ? $this->errors[ $error_code ] : [ ];
		}

		return array_reduce(
			$this->errors,
			function ( array $messages, array $code_errors ) {

				return array_merge( $messages, $code_errors );
			},
			[ ]
		);
	}

	/**
	 * @inheritdoc
	 */
	public function get_error_codes() {

		return $this->errors ? array_keys( $this->errors ) : [ ];
	}

	/**
	 * @inheritdoc
	 */
	public function get_last_message( $error_code = NULL ) {

		if ( empty( $this->errors ) ) {
			return [ ];
		}

		if ( is_null( $error_code ) ) {
			return $this->last_error;
		}

		$this->check_error_code( $error_code );

		if ( ! isset( $this->errors[ $error_code ] ) ) {
			return '';
		}

		$errors = $this->errors[ $error_code ];

		return end( $errors );
	}

	/**
	 * @inheritdoc
	 */
	public function use_error_template( $error_code, $error_template ) {

		$this->check_error_code( $error_code, FALSE );
		$this->check_error_template( $error_template );

		$this->messages[ $error_code ] = $error_template;
	}

	/**
	 * @inheritdoc
	 */
	public function merge( ErrorLoggerInterface $logger ) {

		$merged = clone $this;
		$codes  = $logger->get_error_codes();

		foreach ( $codes as $code ) {

			if ( ! array_key_exists( $code, $this->messages ) ) {
				continue;
			}

			isset( $merged->errors[ $code ] ) or $merged->errors[ $code ] = [ ];
			$merged->errors[ $code ] = array_merge( $merged->errors[ $code ], $logger->get_error_messages( $code ) );
		}

		return $merged;
	}

	/**
	 * @inheritdoc
	 */
	public function count() {

		return array_sum( array_map( 'count', $this->errors ) );
	}

	/**
	 * @inheritdoc
	 */
	public function getIterator() {

		return new \RecursiveIteratorIterator( new \RecursiveArrayIterator( $this->errors ) );
	}

	/**
	 * @param string $code
	 * @param bool   $check_exists
	 */
	private function check_error_code( $code, $check_exists = TRUE ) {

		if ( ! is_string( $code ) ) {
			throw new \InvalidArgumentException(
				sprintf( 'Error code must be in a string, %s given.', gettype( $code ) )
			);
		}

		if ( $check_exists && ! array_key_exists( $code, $this->messages ) ) {

			throw new \InvalidArgumentException( sprintf( '%s is not a valid error code.', $code ) );
		}
	}

	/**
	 * @param string $template
	 */
	private function check_error_template( $template ) {

		if ( ! is_string( $template ) ) {
			throw new \InvalidArgumentException(
				sprintf( 'Error message must be in a string, %s given.', gettype( $template ) )
			);
		}
	}

	/**
	 * @param array $input_data
	 * @param       $error_template
	 *
	 * @return string
	 */
	private function build_message( array $input_data = [ ], $error_template = '' ) {

		if ( ! substr_count( $error_template, '%' ) ) {
			return $error_template;
		}

		array_key_exists( 'value', $input_data ) or $input_data[ 'value' ] = NULL;

		// replacing the placeholder for the %value%
		$message = str_replace( '%value%', $this->as_string( $input_data[ 'value' ] ), $error_template );
		unset( $input_data[ 'value' ] );

		// replacing the placeholder for the %key%
		$data_key = isset( $input_data[ 'key' ] ) ? $input_data[ 'key' ] : '';
		array_key_exists( $data_key, $this->key_labels ) and $data_key = $this->key_labels[ $data_key ];
		$message = str_replace( '%key%', $this->as_string( $data_key ), $message );
		unset( $input_data[ 'key' ] );

		// replacing the possible options-placeholder on the message
		foreach ( $input_data as $key => $replace ) {
			is_numeric( $key ) or $message = str_replace( '%' . $key . '%', $this->as_string( $replace ), $message );
		}

		return $message;

	}

	/**
	 * Returns a string representation of any value.
	 *
	 * @param   mixed $value
	 *
	 * @return  string $type
	 */
	private function as_string( $value ) {

		if ( is_object( $value ) && method_exists( $value, '__toString' ) ) {
			$value = (string) $value;
		}

		if ( is_string( $value ) ) {
			return $value;
		} elseif ( is_null( $value ) ) {
			return 'NULL';
		} elseif ( is_bool( $value ) ) {
			return $value ? '(boolean) TRUE' : '(boolean) FALSE';
		}

		if ( is_object( $value ) ) {
			$value = get_class( $value );
			$type  = '(object) ';
		} elseif ( is_array( $value ) ) {
			$type  = '';
			$value = var_export( $value, TRUE );
		}

		isset( $type ) or $type = '(' . gettype( $value ) . ') ';

		return $type . (string) $value;
	}
}