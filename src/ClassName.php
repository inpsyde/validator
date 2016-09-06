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
 * Class ClassName
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
class ClassName implements ExtendedValidatorInterface {

	use ValidatorDataGetterTrait;
	use GetErrorMessagesTrait;

	/**
	 * @param array $options
	 */
	public function __construct( array $options = [ ] ) {

		$options[ 'autoload' ] = array_key_exists( 'autoload', $options )
			? filter_var( $options[ 'autoload' ], FILTER_VALIDATE_BOOLEAN )
			: TRUE;

		$this->input_data            = $options;
		$this->input_data[ 'value' ] = NULL;
	}

	/**
	 * @inheritdoc
	 */
	public function is_valid( $value ) {

		$this->input_data[ 'value' ] = $value;

		if ( ! is_string( $value ) ) {
			$this->error_code = Error\ErrorLoggerInterface::INVALID_TYPE_NON_STRING;
			$this->update_error_messages();

			return FALSE;
		}

		if ( class_exists( $value, $this->input_data[ 'autoload' ] ) ) {
			return TRUE;
		}

		$this->error_code = Error\ErrorLoggerInterface::NOT_CLASS_NAME;
		$this->update_error_messages();

		return FALSE;
	}

}