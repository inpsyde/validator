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
	 * @inheritdoc
	 */
	public function log_error( $error_code, $error_message = NULL ) {

		$this->check_error_code( $error_code );
		is_null( $error_message )
			? $this->check_error_message( $error_message )
			: $error_message = $this->messages[ $error_code ];

		isset( $this->errors[ $error_code ] ) or $this->errors[ $error_code ] = [ ];
		$this->errors[ $error_code ][] = $error_message;
		$this->last_error              = $error_message;

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function get_error_messages() {

		if ( empty( $this->errors ) ) {
			return [ ];
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
	public function use_error_message( $error_code, $custom_message ) {

		$this->check_error_code( $error_code );
		$this->check_error_message( $custom_message );

		$this->messages[ $error_code ] = $custom_message;
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
	 * @param string $message
	 */
	private function check_error_message( $message ) {

		if ( ! is_string( $message ) ) {
			throw new \InvalidArgumentException(
				sprintf( 'Error message must be in a string, %s given.', gettype( $message ) )
			);
		}
	}
}