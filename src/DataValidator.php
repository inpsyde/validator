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
	 * @var array
	 */
	private $key_labels = [ ];

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
	 * @inheritdoc
	 */
	public function add_validator_with_message( ExtendedValidatorInterface $validator, $error_message ) {

		return $this->add_validator_to_stack( $validator, self::GENERIC_VALIDATOR_KEY, $error_message );
	}

	/**
	 * @inheritdoc
	 */
	public function add_validator_by_key( ExtendedValidatorInterface $validator, $key, $error_message = NULL ) {

		$keys = $this->parse_validator_key( $key );

		if ( ! is_array( $keys ) ) {
			throw new Exception\InvalidArgumentException( 'Validator key must be in a string or an array of string.' );
		}

		array_walk(
			$keys,
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

		return $this->error_logger->get_error_codes();
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

		return array_sum( array_map( 'count', $this->validators ) );
	}

	/**
	 * @inheritdoc
	 */
	public function is_valid( $value ) {

		if ( empty( $this->validators ) ) {
			return TRUE;
		}

		if ( ! is_array( $value ) && ! $value instanceof \Traversable ) {
			$this->input_data = [ 'value' => $value ];
			$this->error_code = ErrorLoggerInterface::INVALID_TYPE_NON_TRAVERSABLE;
			isset( $this->error_data[ $this->error_code ] ) or $this->error_data[ $this->error_code ] = [ ];
			$this->error_data[ $this->error_code ][] = $this->input_data;

			$this->error_logger->log_error( $this->error_code, $this->error_data );

			return FALSE;
		}

		// If value is empty, noting to validate, just return true
		if ( $value === [ ] || ( $value instanceof \Countable && ! count( $value ) ) ) {
			return TRUE;
		}

		$valid = TRUE;

		/** @var \SplObjectStorage $generic */
		$generic = isset( $this->validators[ self::GENERIC_VALIDATOR_KEY ] )
			? $this->validators[ self::GENERIC_VALIDATOR_KEY ]
			: [ ];

		if ( $generic ) {
			foreach ( $value as $key => $item ) {
				$valid = $this->validate_validators( $generic, $item, (string) $key ) && $valid;
			}
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

		return $this->error_logger->get_error_messages();
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
	 * @param string            $key
	 *
	 * @return bool
	 */
	private function validate_validators( \SplObjectStorage $validators, $to_validate, $key ) {

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

		if ( $validator instanceof MultiValidatorInterface ) {
			$data = $validator->get_error_data();
			foreach ( $data as $code => $code_data ) {
				foreach ( $code_data as $error_data ) {
					$this->validator_log_error( $code, $error_data, $key, $message );
				}
			}

			return FALSE;
		}

		$this->validator_log_error( $validator->get_error_code(), $validator->get_input_data(), $key, $message );

		return FALSE;
	}

	/**
	 * @param string $code
	 * @param array  $data
	 * @param string $key
	 * @param string $message
	 */
	private function validator_log_error( $code, array $data, $key, $message ) {

		$this->input_data          = $data;
		$this->input_data[ 'key' ] = $key;
		array_key_exists( $key, $this->key_labels ) and $key = [ $key => $this->key_labels[ $key ] ];
		$this->error_code = $code;
		$this->error_logger->log_error_for_key( $key, $code, $this->input_data, $message );
		isset( $this->error_data[ $code ] ) or $this->error_data[ $code ] = [ ];
		$this->error_data[ $code ][] = $this->input_data;
	}

	/**
	 * Parse the `$key` argument for `add_validator_by_key()` and return an array of key to which assign the validator.
	 *
	 * - When provided a single key as string, return an array with a single element containing that key.
	 * - When provided more keys as strings, return all of them.
	 * - It is also possible to provide a "label" for the key that will be used for replacement in error messages.
	 *   To do that, each key have to be provided as 2-element array with keys "key" and "label",
	 *   e.g. ['key' => 'foo', 'label' => __('Foo Element')]
	 *
	 * @param string|array $key
	 *
	 * @return string[]
	 */
	private function parse_validator_key( $key ) {

		( is_string( $key ) && $key ) and $key = [ $key ];

		if ( ! is_array( $key ) ) {
			throw new Exception\InvalidArgumentException( 'Validator key must be in a string or an array of string.' );
		}

		$maybe_label = function ( $key ) {

			if (
				is_array( $key )
				&& ! empty( $key[ 'key' ] )
				&& ! empty( $key[ 'label' ] )
				&& is_string( $key[ 'key' ] )
				&& is_string( $key[ 'label' ] )
			) {
				$this->key_labels[ $key[ 'key' ] ] = $key[ 'label' ];

				return $key[ 'key' ];
			}

			return NULL;
		};

		$label = $maybe_label( $key );
		if ( $label ) {
			return [ $label ];
		}

		$keys = [ ];
		foreach ( $key as $k ) {

			if ( is_string( $k ) ) {
				$keys[] = $k;
				continue;
			}

			$label = NULL;
			if ( is_array( $k ) ) {
				$label = $maybe_label( $k );
				$label and $keys[] = $label;
			}

			$more_keys = [ ];
			if ( is_array( $k ) && ! $label ) {
				$more_keys = array_filter( $k, 'is_string' );
				$more_keys and $keys = array_merge( $keys, $more_keys );
			}

			if ( ! $more_keys ) {
				throw new Exception\InvalidArgumentException(
					'Validator key must be in a string or an array of string.'
				);
			}
		}

		return $keys;
	}
}