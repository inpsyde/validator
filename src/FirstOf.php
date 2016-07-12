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
 * Class FirstOf
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
class FirstOf implements ExtendedValidatorInterface, MultiValidatorInterface {

	use ValidatorDataGetterTrait;
	use GetErrorMessagesTrait;
	use MultiValidatorDataGetterTrait;
	use MultiValidatorValidatorsTrait;

	/**
	 * FirstOf constructor.
	 *
	 * @param array                        $options
	 * @param ExtendedValidatorInterface[] $validators
	 */
	public function __construct( array $options = [ ], array $validators = [ ] ) {

		$factory = new ValidatorFactory();

		foreach ( $validators as $validator ) {

			$options = [ ];

			if ( is_array( $validator ) && isset( $validator[ 'validator' ] ) ) {
				isset( $validator[ 'options' ] ) and $options = (array) $validator[ 'options' ];
				$validator = $validator[ 'validator' ];
			}

			$instance           = $factory->create( $validator, $options );
			$this->validators[] = $instance;
		}
	}

	/**
	 * @inheritdoc
	 */
	public function is_valid( $value ) {

		$this->input_data = [ 'value' => $value ];

		$error_data = [ ];
		$error_code = '';

		foreach ( $this->validators as $validator ) {

			if ( $validator->is_valid( $value ) ) {
				return TRUE;
			}

			$data             = $validator->get_input_data();
			$data[ 'value' ]  = $value;
			$this->input_data = $data;
			$error_code       = $validator->get_error_code();
			isset( $error_data[ $error_code ] ) or $error_data[ $error_code ] = [ ];
			$error_data[ $error_code ][] = $data;
		}

		$this->error_data = $error_data;
		$this->error_code = $error_code;

		$this->update_error_messages();

		return FALSE;
	}

}