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
 * Class Size
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
class Size implements ExtendedValidatorInterface {

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

		$this->options[ 'size' ] = isset( $options[ 'size' ] ) && is_numeric( $options[ 'size' ] )
			? (int) $options[ 'size' ]
			: 0;

		$this->input_data            = $this->options;
		$this->input_data[ 'value' ] = NULL;

	}

	/**
	 * @inheritdoc
	 */
	public function is_valid( $value ) {

		$this->input_data = [ 'value' => $value ];

		if ( is_resource( $value ) || ( is_object( $value ) && ! $value instanceof \Countable ) ) {
			$this->error_code = Error\ErrorLoggerInterface::INVALID_TYPE_NON_COUNTABLE;
			$this->update_error_messages();

			return FALSE;
		}

		if ( $this->calc_size( $value ) === $this->options[ 'size' ] ) {
			return TRUE;
		}

		$this->error_code = Error\ErrorLoggerInterface::INVALID_SIZE;
		$this->update_error_messages();

		return FALSE;
	}

	/**
	 * @param mixed $value
	 *
	 * @return int
	 */
	private function calc_size( $value ) {

		$type = gettype( $value );

		switch ( $type ) {
			case 'string' :
				return function_exists( 'mb_strlen' ) ? mb_strlen( $value ) : strlen( $value );
			case 'integer' :
			case 'double' :
			case 'boolean' :
				return (int) $value;
			case 'array' :
			case 'object' :
				return count( $value );
		}

		return 0;

	}

}