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

use Inpsyde\Validator\Error\ErrorLoggerInterface;

/**
 * Class Negate
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
class Negate implements SecondaryValidatorInterface {

	use ValidatorDataGetterTrait;
	use GetErrorMessagesTrait;

	/**
	 * @inheritdoc
	 * @return Negate
	 */
	public static function with_validator( ExtendedValidatorInterface $validator ) {

		return new static( [ 'validator' => $validator ] );
	}

	/**
	 * @param array $options
	 */
	public function __construct( array $options = [ ] ) {

		if ( empty( $options[ 'validator' ] ) ) {
			throw new \InvalidArgumentException(
				sprintf( '%s "validator" option must be a validators instance or identifier.', __CLASS__ )
			);
		}

		$validator_id = $options[ 'validator' ];
		$options      = empty( $options[ 'options' ] ) || ! is_array( $options[ 'options' ] )
			? [ ]
			: $options[ 'options' ];

		$factory   = new ValidatorFactory();
		$validator = $factory->create( $validator_id, $options );

		if ( ! $validator instanceof ExtendedValidatorInterface ) {
			throw new \InvalidArgumentException(
				sprintf( '%s "validator" option must be a validators instance or identifier.', __CLASS__ )
			);
		}

		$this->input_data                     = $options;
		$this->input_data[ 'validator' ]      = $validator;
		$class_parts                          = explode( '\\', get_class( $validator ) );
		$this->input_data[ 'validator_name' ] = end( $class_parts );
		$this->input_data[ 'value' ]          = NULL;
	}

	/**
	 * @inheritdoc
	 */
	public function is_valid( $value ) {

		$this->input_data[ 'value' ] = $value;

		/** @var ExtendedValidatorInterface $validator */
		$validator = $this->input_data[ 'validator' ];
		$valid     = $validator->is_valid( $value );

		if ( $valid ) {
			$this->error_code = ErrorLoggerInterface::NOT_NEGATE_VALIDATOR;
			$this->update_error_messages();
		}

		return ! $valid;
	}

}