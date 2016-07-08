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
 * Class Date
 *
 * @author  Christian Brückner <chris@chrico.info>
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
class Date implements ExtendedValidatorInterface {

	use ValidatorDataGetterTrait;
	use GetErrorMessagesTrait;

	/**
	 * @deprecated Error codes are now defined in Error\ErrorLoggerInterface
	 */
	const INVALID_TYPE = Error\ErrorLoggerInterface::INVALID_TYPE_NON_DATE;

	/**
	 * @deprecated Error codes are now defined in Error\ErrorLoggerInterface
	 */
	const INVALID_DATE = Error\ErrorLoggerInterface::INVALID_DATE;

	/**
	 * @deprecated Error codes are now defined in Error\ErrorLoggerInterface
	 */
	const INVALID_FORMAT = Error\ErrorLoggerInterface::INVALID_DATE_FORMAT;

	/**
	 * @var array
	 */
	protected $options = [ ];

	/**
	 * @var array
	 * @deprecated
	 */
	protected $message_templates = [
		Error\ErrorLoggerInterface::INVALID_TYPE_NON_DATE => "Invalid type given. String, integer, array or DateTime expected.",
		Error\ErrorLoggerInterface::INVALID_DATE          => "The input does not appear to be a valid date",
		Error\ErrorLoggerInterface::INVALID_DATE_FORMAT   => "The input does not fit the date format '%format%'",
	];

	/**
	 * @param array $options
	 */
	public function __construct( array $options = [ ] ) {

		$this->options[ 'format' ] = isset( $options[ 'format' ] ) && is_string( $options[ 'format' ] )
			? $options[ 'format' ]
			: 'd.m.Y';

		$this->input_data            = $this->options;
		$this->input_data[ 'value' ] = NULL;

	}

	/**
	 * @inheritdoc
	 */
	public function is_valid( $value ) {

		$this->input_data[ 'value' ] = $value;

		if ( ! $this->convert_to_date_time( $value ) instanceof \DateTimeInterface ) {
			$this->error_code or $this->error_code = Error\ErrorLoggerInterface::INVALID_DATE;
			$this->update_error_messages();

			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Attempts to convert an int, string, or array to a DateTime object.
	 *
	 * @param  string|int|array $value
	 *
	 * @return bool|\DateTime
	 */
	protected function convert_to_date_time( $value ) {

		if ( $value instanceof \DateTimeInterface ) {
			return $value;
		}

		switch ( gettype( $value ) ) {
			case 'string' :
				return $this->convert_string( $value );
			case 'integer' :
				return $this->convert_integer( $value );
			case 'double' :
				return $this->convert_double( $value );
			case 'array' :
				return $this->convert_array( $value );
		}

		$this->error_code = Error\ErrorLoggerInterface::INVALID_TYPE_NON_DATE;

		return FALSE;
	}

	/**
	 * Attempts to convert an integer into a DateTime object.
	 *
	 * @param  integer $value
	 *
	 * @return bool|\DateTime
	 */
	protected function convert_integer( $value ) {

		return date_create( "@$value" );
	}

	/**
	 * Attempts to convert an double into a DateTime object.
	 *
	 * @param  double $value
	 *
	 * @return bool|\DateTime
	 */
	protected function convert_double( $value ) {

		return \DateTime::createFromFormat( 'U', $value );
	}

	/**
	 * Attempts to convert a string into a DateTime object.
	 *
	 * @param  string $value
	 *
	 * @return bool|\DateTime
	 */
	protected function convert_string( $value ) {

		$format = $this->options[ 'format' ];
		$date   = \DateTime::createFromFormat( $format, $value );

		// Invalid dates can show up as warnings (ie. "2007-02-99") and still return a DateTime object.
		$errors = \DateTime::getLastErrors();
		if ( $errors[ 'warning_count' ] > 0 ) {
			$this->error_code = Error\ErrorLoggerInterface::INVALID_DATE_FORMAT;

			return FALSE;
		}

		return $date;
	}

	/**
	 * Implodes the array into a string and proxies to convert_string().
	 *
	 * @param  array $value
	 *
	 * @return bool|\DateTime
	 */
	protected function convert_array( array $value ) {

		return $this->convert_string( implode( '-', $value ) );
	}

}