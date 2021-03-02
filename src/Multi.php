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
	use MultiValidatorDataGetterTrait;
	use MultiValidatorValidatorsTrait;

	/**
	 * @var bool
	 */
	private $stop_on_failure = FALSE;

	/**
	 * Named constructor
	 *
	 * @return Multi
	 */
	public static function with_validators() {

		return new static( [ 'validators' => func_get_args() ] );
	}

	/**
	 * Multi constructor.
	 *
	 * @param array                        $options
	 * @param ExtendedValidatorInterface[] $validators
	 */
	public function __construct( array $options = [ ], array $validators = [ ] ) {

		$this->stop_on_failure = array_key_exists( 'stop_on_failure', $options )
			? filter_var( $options[ 'stop_on_failure' ], FILTER_VALIDATE_BOOLEAN )
			: FALSE;

		if ( array_key_exists( 'logger', $options ) ) {
		    $this->logger = $options['logger'];
        }

		$factory = new ValidatorFactory();

		array_key_exists( 'validators', $options ) and $validators = array_merge(
			(array) $options[ 'validators' ],
			$validators
		);

		foreach ( $validators as $validator ) {

			$options = [ ];

			if ( is_array( $validator ) && isset( $validator[ 'validator' ] ) ) {
				isset( $validator[ 'options' ] ) and $options = (array) $validator[ 'options' ];
				$validator = $validator[ 'validator' ];
			}

			$this->add_validator( $factory->create( $validator, $options ) );
		}
	}

	/**
	 * @inheritdoc
	 */
	public function is_valid( $value ) {

		$this->input_data = [ 'value' => $value ];
		$valid            = TRUE;

		foreach ( $this->validators as $validator ) {
			if ( ! $validator->is_valid( $value ) ) {

				$data             = $validator->get_input_data();
				$data[ 'value' ]  = $value;
				$this->input_data = $data;
				$code             = $validator->get_error_code();
				$this->error_code = $code;
				isset( $this->error_data[ $code ] ) or $this->error_data[ $code ] = [ ];
				$this->error_data[ $code ][] = $data;
				$valid                       = FALSE;
			}

			if ( ! $valid && $this->stop_on_failure ) {
				$this->update_error_messages();

				return FALSE;
			}
		}

		if ( $valid ) {
			$this->input_data = [ 'value' => NULL ];
			$this->error_code = '';
		}

		return $valid;
	}

	/**
	 * Return an instance of the class that will stop at first failure.
	 *
	 * @return Multi
	 */
	public function stop_on_failure() {

		$validator                               = clone $this;
		$validator->stop_on_failure = TRUE;

		return $validator;
	}
}
