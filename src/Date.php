<?php
namespace Inpsyde\Validator;

/**
 * Class Date
 *
 * @package Inpsyde\Validator
 */
class Date extends AbstractValidator {

	const INVALID = 'dateInvalid';
	const INVALID_DATE = 'dateInvalidDate';
	const FALSE_FORMAT = 'dateFalseFormat';

	/**
	 * @var array
	 */
	protected $message_templates = [
		self::INVALID      => "Invalid type given. String, integer, array or DateTime expected",
		self::INVALID_DATE => "The input does not appear to be a valid date",
		self::FALSE_FORMAT => "The input does not fit the date format '%format%'",
	];

	/**
	 * @var array
	 */
	protected $options = [
		'format' => 'd.m.Y',
	];

	/**
	 * {@inheritdoc}
	 */
	public function is_valid( $value ) {

		if ( ! $this->convert_to_date_time( $value ) ) {
			$this->set_error_message( self::INVALID_DATE, $value );

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

		if ( $value instanceof \DateTime || $value instanceof \DateTimeInterface ) {
			return $value;
		}

		$type = gettype( $value );
		if ( ! in_array( $type, [ 'string', 'integer', 'double', 'array' ] ) ) {
			$this->set_error_message( self::INVALID, $value );

			return FALSE;
		}

		$convertMethod = 'convert_' . $type;

		return $this->{$convertMethod}( $value );
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
		// Invalid dates can show up as warnings (ie. "2007-02-99")
		// and still return a DateTime object.
		$errors = \DateTime::getLastErrors();
		if ( $errors[ 'warning_count' ] > 0 ) {
			$this->set_error_message( self::FALSE_FORMAT, $value );

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