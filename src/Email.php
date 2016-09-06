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
 * Class Email
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
class Email implements ExtendedValidatorInterface {

	use ValidatorDataGetterTrait;
	use GetErrorMessagesTrait;

	/**
	 * @param array $options
	 */
	public function __construct( array $options = [ ] ) {

		$options[ 'check_dns' ] = isset( $options[ 'check_dns' ] )
			? filter_var( $options[ 'check_dns' ], FILTER_VALIDATE_BOOLEAN )
			: FALSE;

		$this->input_data            = $options;
		$this->input_data[ 'value' ] = NULL;
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_valid( $value ) {

		$this->input_data[ 'value' ] = $value;

		( is_object( $value ) && method_exists( $value, '__toString' ) ) and $value = (string) $value;

		if ( ! is_string( $value ) ) {
			$this->error_code = Error\ErrorLoggerInterface::INVALID_TYPE_NON_STRING;
			$this->update_error_messages();

			return FALSE;

		}

		if ( ! $value ) {
			$this->error_code = Error\ErrorLoggerInterface::IS_EMPTY;
			$this->update_error_messages();

			return FALSE;
		}

		if ( ! filter_var( $value, FILTER_VALIDATE_EMAIL ) ) {
			$this->error_code = Error\ErrorLoggerInterface::NOT_EMAIL;
			$this->update_error_messages();

			return FALSE;
		}

		if ( ! $this->input_data[ 'check_dns' ] ) {
			return TRUE;
		}

		$parts = explode( '@', $value, 2 );

		if ( ! checkdnsrr( end( $parts ), 'ANY' ) ) {
			$this->error_code = Error\ErrorLoggerInterface::INVALID_DNS;
			$this->update_error_messages();

			return FALSE;
		}

		return TRUE;
	}

}