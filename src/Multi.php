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

/**
 * Class Multi
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
class Multi implements ExtendedValidatorInterface, MultiValidatorInterface {

	use ValidatorDataGetterTrait;
	use GetErrorMessagesTrait;

	/**
	 * @var ExtendedValidatorInterface[]
	 */
	private $validators = [ ];

	/**
	 * @var \SplObjectStorage
	 */
	private $validator_input_data;

	/**
	 * @var array
	 */
	private $error_data = [ ];

	/**
	 * @var array
	 */
	private $options = [ ];

	/**
	 * Named constructor that can be used obtain an instance by passing a variadic number of validators.
	 */
	public function withValidators() {

		return new static( [ ], func_get_args() );
	}

	/**
	 * Multi constructor.
	 *
	 * @param array                        $options
	 * @param ExtendedValidatorInterface[] $validators
	 */
	public function __construct( array $options = [ ], array $validators = [ ] ) {

		$this->options = isset( $options[ 'stop_on_failure' ] )
			? filter_var( $options[ 'stop_on_failure' ], FILTER_VALIDATE_BOOLEAN )
			: FALSE;

		$factory                    = new ValidatorFactory();
		$this->validator_input_data = new \SplObjectStorage();

		foreach ( $validators as $validator ) {

			$options = [ ];

			if ( is_array( $validator ) && isset( $validator[ 'validator' ] ) ) {
				isset( $validator[ 'options' ] ) and $options = (array) $validator[ 'options' ];
				$validator = $validator[ 'validator' ];
			}

			$instance = $factory->create( $validator, $options );
			$this->validator_input_data->attach( $instance, $options );
			$this->validators[] = $instance;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function add_validator( ExtendedValidatorInterface $validator ) {

		$this->validators[] = $validator;

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function is_valid( $value ) {

		$this->input_data = [ 'value' => $value ];
		$valid            = TRUE;

		foreach ( $this->validators as $validator ) {
			if ( ! $validator->is_valid( $value ) ) {

				$data             = $this->validator_input_data->offsetGet( $validator );
				$data[ 'value' ]  = $value;
				$this->input_data = $data;
				$code             = $validator->get_error_code();
				$this->error_code = $code;
				isset( $this->error_data[ $code ] ) or $this->error_data[ $code ] = [ ];
				$this->error_data[ $code ][] = $data;
				$valid                       = FALSE;
			}

			if ( ! $valid && $this->options[ 'stop_on_failure' ] ) {
				return FALSE;
			}
		}

		return $valid;
	}

	/**
	 * @inheritdoc
	 */
	public function get_error_codes() {

		return array_keys( $this->error_data );
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

		return count( $this->validators );
	}

	/**
	 * Return an instance of the class that will stop at first failure.
	 *
	 * @return Multi
	 */
	public function stopOnFailure() {

		$validator                               = clone $this;
		$validator->options[ 'stop_on_failure' ] = TRUE;

		return $validator;
	}
}