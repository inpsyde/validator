<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the inpsyde-validator package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inpsyde\Validator;

use Inpsyde\Validator\Error\ErrorLoggerFactory;
use Inpsyde\Validator\Error\ErrorLoggerInterface;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package inpsyde-validator
 */
final class DataValidator implements MapValidatorInterface, ErrorLoggerAwareValidatorInterface {

	use ValidatorDataGetterTrait;

	const GENERIC_VALIDATOR_KEY = - 1;

	/**
	 * @var Error\ErrorLoggerInterface
	 */
	private $error_logger;

	/**
	 * @var \SplObjectStorage[]
	 */
	private $validators = [ ];

	/**
	 * @var ValidatorFactory
	 */
	private $validator_factory;

	/**
	 * @var array
	 */
	private $error_data = [ ];

	/**
	 * @param Error\ErrorLoggerInterface $error_logger
	 */
	public function __construct( Error\ErrorLoggerInterface $error_logger = NULL ) {

		$this->validator_factory = new ValidatorFactory();
		$this->error_logger      = $error_logger ? : ( new ErrorLoggerFactory() )->get_logger();
	}

	/**
	 * @inheritdoc
	 */
	public function with_error_logger( Error\ErrorLoggerInterface $error_logger ) {

		$instance               = clone $this;
		$instance->error_logger = $instance->error_logger->merge( $error_logger );

		return $instance;
	}

	/**
	 * @inheritdoc
	 */
	public function add_validator( ExtendedValidatorInterface $validator ) {

		return $this->add_validator_to_stack( $validator, self::GENERIC_VALIDATOR_KEY );
	}

	/**
	 * Adds a "leaf" validator to validate a specific key or a set of keys.
	 *
	 * @param ExtendedValidatorInterface $validator
	 * @param string|string[]            $key
	 * @param string|null                $error_message
	 *
	 * @inheritdoc
	 */
	public function add_validator_by_key( ExtendedValidatorInterface $validator, $key, $error_message = NULL ) {

		$key = ( is_string( $key ) && $key ) ? [ $key ] : '';
		is_array( $key ) and $key = array_filter( $key, 'is_string' );

		if ( ! $key || ! ! is_array( $key ) ) {
			throw new Exception\InvalidArgumentException( 'Validator key must be in a string or an array of string.' );
		}

		array_walk(
			$key,
			function ( $key ) use ( $validator, $error_message ) {

				return $this->add_validator_to_stack( $validator, $key, $error_message );
			}
		);

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function add_validator_map( array $validators ) {

		array_walk(
			$validators,
			function ( $validator, $key ) {

				is_string( $key ) and $this->add_validator_to_stack( $validator, $key );
			}
		);

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function get_error_codes() {

		$this->error_logger->get_error_codes();
	}

	/**
	 * @inheritdoc
	 */
	public function get_error_data( $error_code = NULL ) {

		if ( is_null( $error_code ) ) {
			return $this->error_data;
		} elseif ( ! isset( $this->error_data[ $error_code ] ) ) {
			return [ ];
		}

		return $this->error_data[ $error_code ];
	}

	/**
	 * @inheritdoc
	 */
	public function count() {

	}

	/**
	 * @inheritdoc
	 */
	public function is_valid( $value ) {

		if ( ! is_array( $value ) && ! $value instanceof \Traversable ) {
			$this->input_data = [ 'value' => $value ];
			$this->error_code = ErrorLoggerInterface::INVALID_TYPE_NON_TRAVERSABLE;
			isset( $this->error_data[ $this->error_code ] ) or $this->error_data[ $this->error_code ] = [ ];
			$this->error_data[ $this->error_code ][] = $this->input_data;

			$this->error_logger->log_error( $this );

			return FALSE;
		}

		$valid = TRUE;

		/** @var \SplObjectStorage $generic */
		$generic = isset( $this->validators[ self::GENERIC_VALIDATOR_KEY ] )
			? $this->validators[ self::GENERIC_VALIDATOR_KEY ]
			: [ ];

		foreach ( $value as $item ) {
			$valid = $this->validate_validators( $generic, $item ) && $valid;
		}

		foreach ( $this->validators as $key => $validators ) {
			if ( $key !== self::GENERIC_VALIDATOR_KEY ) {
				$to_validate = isset( $value[ $key ] ) ? $value[ $key ] : NULL;
				$valid       = $this->validate_validators( $validators, $to_validate, $key ) && $valid;
			}
		}

		return $valid;
	}

	/**
	 * @inheritdoc
	 */
	public function get_error_messages() {

		$this->error_logger->get_error_messages();
	}

	/**
	 * @param ExtendedValidatorInterface|string $validator
	 * @param string|int                        $key
	 * @param string|null                       $error_message
	 *
	 * @return static
	 */
	private function add_validator_to_stack( $validator, $key, $error_message = NULL ) {

		$options = [ ];

		if ( is_array( $validator ) && isset( $validator[ 'validator' ] ) ) {
			isset( $validator[ 'options' ] ) and $options = (array) $validator[ 'options' ];
			$validator = $validator[ 'validator' ];
		}

		$validator = $this->validator_factory->create( $validator, $options );

		isset( $this->validators[ $key ] ) or $this->validators[ $key ] = new \SplObjectStorage();
		$this->validators[ $key ]->attach( $validator, $error_message );

		return $this;

	}

	/**
	 * @param \SplObjectStorage $validators
	 * @param                   $to_validate
	 * @param string|null       $key
	 *
	 * @return bool
	 */
	private function validate_validators( \SplObjectStorage $validators, $to_validate, $key = NULL ) {

		$valid = TRUE;

		$validators->rewind();
		while ( $validators->valid() ) {

			/** @var ExtendedValidatorInterface $validator */
			$validator = $validators->current();
			/** @var string|null $message */
			$message = $validators->getInfo();

			$valid = $this->validate_validator( $to_validate, $validator, $key, $message ) && $valid;

			$validators->next();
		}

		return $valid;

	}

	/**
	 * @param                            $value
	 * @param ExtendedValidatorInterface $validator
	 * @param string|null                $key
	 * @param string|null                $message
	 *
	 * @return bool
	 */
	private function validate_validator( $value, ExtendedValidatorInterface $validator, $key, $message ) {

		$valid = $validator->is_valid( $value );
		if ( $valid ) {
			return TRUE;
		}

		$codes = $validator instanceof MultiValidatorInterface
			? $validator->get_error_codes()
			: (array) $validator->get_error_code();

		foreach ( $codes as $code ) {
			$this->error_code = $code;
			$this->input_data = $validator->get_input_data();
			$key and $this->input_data[ 'key' ] = $key;
			$this->error_logger->log_error( $this, $message );
			isset( $this->error_data[ $code ] ) or $this->error_data[ $code ] = [ ];
			$this->error_data[ $code ][] = $this->input_data;
		}

		return FALSE;

	}
}