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
	 * Constructor.
	 *
	 * @param string[] $messages An array of messages to replace default ones.
	 */
	public function __construct( array $messages = [ ] ) {

		$default = [
			self::INVALID_TYPE_NON_STRING      => __(
				'Invalid type given for <code>%value%</code>. String expected.',
				'inpsyde-validator'
			),
			self::INVALID_TYPE_NON_NUMERIC     => __(
				'Invalid type given for <code>%value%</code>. Integer or float expected.',
				'inpsyde-validator'
			),
			self::INVALID_TYPE_NON_SCALAR      => __(
				'Invalid type given for <code>%value%</code>. String, integer or float expected.',
				'inpsyde-validator'
			),
			self::INVALID_TYPE_NON_TRAVERSABLE => __(
				'Invalid type given for <code>%value%</code>. Array or object implementing Traversable expected.',
				'inpsyde-validator'
			),
			self::INVALID_TYPE_NON_DATE        => __(
				'Invalid type given for <code>%value%</code>. String, integer, array or DateTime expected.',
				'inpsyde-validator'
			),
			self::NOT_BETWEEN                  => __(
				'The input <code>%value%</code> is not between <code>%min%</code> and <code>%max%</code>, inclusively.',
				'inpsyde-validator'
			),
			self::NOT_BETWEEN_STRICT           => __(
				'The input <code>%value%</code> is not strictly between <code>%min%</code> and <code>%max%</code>.',
				'inpsyde-validator'
			),
			self::INVALID_DATE                 => __(
				'The input <code>%value%</code> does not appear to be a valid date.',
				'inpsyde-validator'
			),
			self::INVALID_DATE_FORMAT          => __(
				'The input <code>%value%</code> does not fit the date format <code>%format%</code>.',
				'inpsyde-validator'
			),
			self::NOT_GREATER                  => __(
				'The input <code>%value%</code> is not greater than <code>%min%</code>.',
				'inpsyde-validator'
			),
			self::NOT_GREATER_INCLUSIVE        => __(
				'The input <code>%value%</code> is not greater or equal than <code>%min%</code>.',
				'inpsyde-validator'
			),
			self::NOT_IN_ARRAY                 => __(
				'The input <code>%value%</code> is not in the haystack: <code>%haystack%</code>.',
				'inpsyde-validator'
			),
			self::NOT_LESS                     => __(
				'The input <code>%value%</code> is not less than <code>%max%</code>.',
				'inpsyde-validator'
			),
			self::NOT_LESS_INCLUSIVE           => __(
				'The input <code>%value%</code> is not less or equal than <code>%max%</code>.',
				'inpsyde-validator'
			),
			self::IS_EMPTY                     => __(
				'This value should not be empty.',
				'inpsyde-validator'
			),
			self::NOT_MATCH                    => __(
				'The input does not match against pattern <code>%pattern%</code>.',
				'inpsyde-validator'
			),
			self::REGEX_INTERNAL_ERROR         => __(
				'There was an internal error while using the pattern <code>%pattern%</code>.',
				'inpsyde-validator'
			),
			self::NOT_URL                      => __(
				'The input <code>%value%</code> is not a valid URL.',
				'inpsyde-validator'
			),
			self::INVALID_DNS                  => __(
				'The host for the given input <code>%value%</code> could not be resolved.',
				'inpsyde-validator'
			),
			self::MULTIPLE_ERRORS              => __(
				'The host for the given input <code>%value%</code> could not be resolved.',
				'inpsyde-validator'
			),
		];

		$this->messages = array_merge( $default, array_filter( $messages, 'is_string' ) );
	}

	/**
	 * @inheritdoc
	 */
	public function log_error( ExtendedValidatorInterface $validator, $error_template = NULL ) {

		$code = $validator->get_error_code();

		$this->check_error_code( $code );
		is_null( $error_template )
			? $this->check_error_template( $error_template )
			: $error_template = $this->messages[ $code ];

		$error_message = $this->build_message( $validator, $error_template );

		isset( $this->errors[ $code ] ) or $this->errors[ $code ] = [ ];
		$this->errors[ $code ][] = $error_message;
		$this->last_error        = $error_message;

		return $this;
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

				foreach ( $code_errors as $error ) {
					$messages[] = $error;
				}
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

		$this->check_error_code( $error_code );
		$this->check_error_template( $error_template );

		$this->messages[ $error_code ] = $error_template;
	}

	/**
	 * @inheritdoc
	 */
	public function count() {

		return array_sum( array_count_values( $this->errors ) );
	}

	/**
	 * @inheritdoc
	 */
	public function getIterator() {

		return new \RecursiveIteratorIterator( new \RecursiveArrayIterator( $this->errors ) );
	}

	/**
	 * @param string $code
	 */
	private function check_error_code( $code ) {

		if ( ! is_string( $code ) ) {
			throw new \InvalidArgumentException(
				sprintf( 'Error code must be in a string, %s given.', gettype( $code ) )
			);
		}

		if ( ! defined( "static::{$code}" ) ) {

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
	 * @param ExtendedValidatorInterface   $validator
	 *
	 * @param                              $error_template
	 *
	 * @return string
	 */
	private function build_message( ExtendedValidatorInterface $validator, $error_template ) {

		if ( ! substr_count( $error_template, '%' ) ) {
			return $error_template;
		}

		$input_data = (array) $validator->get_input_data();

		if ( ! isset( $input_data[ 'value' ] ) ) {

			return vsprintf( $error_template, array_map( [ $this, 'as_string' ], $input_data ) );
		}

		// replacing the placeholder for the %value%
		$message = str_replace( '%value%', $this->as_string( $input_data[ 'value' ] ), $error_template );

		unset( $input_data[ 'value' ] );

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
		} elseif ( is_array( $value ) ) {
			$type  = '';
			$value = var_export( $value, TRUE );
		}

		isset( $type ) or $type = '(' . gettype( $value ) . ') ';

		return $type . (string) $value;
	}
}