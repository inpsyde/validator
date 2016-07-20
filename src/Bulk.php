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
 * Class Bulk
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
class Bulk implements ExtendedValidatorInterface {

	use ValidatorDataGetterTrait;
	use GetErrorMessagesTrait;

	/**
	 * @var array
	 */
	protected $options = [ ];

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

		$this->options[ 'validator' ] = $validator;
		$this->input_data             = $this->options;
		$this->input_data[ 'value' ]  = NULL;
	}

	/**
	 * @inheritdoc
	 */
	public function is_valid( $value ) {

		$this->input_data = [ 'value' => $value ];

		if ( ! is_array( $value ) && ! $value instanceof \Traversable ) {
			$this->error_code = Error\ErrorLoggerInterface::INVALID_TYPE_NON_TRAVERSABLE;
			$this->update_error_messages();

			return FALSE;
		}

		$valid = TRUE;
		/** @var ExtendedValidatorInterface $validator */
		$validator = $this->options[ 'validator' ];

		foreach ( $value as $item ) {
			if ( ! $validator->is_valid( $item ) ) {
				$this->input_data = $validator->get_input_data();
				$valid            = FALSE;
				break;
			}
		}

		if ( $valid ) {
			return TRUE;
		}

		$this->error_code = $validator->get_error_code();
		$this->update_error_messages();

		return FALSE;
	}

}