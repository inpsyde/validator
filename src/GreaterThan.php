<?php
namespace Inpsyde\Validator;

/**
 * Class GreaterThan
 *
 * @package Inpsyde\Validator
 */
class GreaterThan extends AbstractValidator {

	const NOT_GREATER = 'notGreaterThan';
	const NOT_GREATER_INCLUSIVE = 'notGreaterThanInclusive';

	/**
	 * @var array
	 */
	protected $message_templates = [
		self::NOT_GREATER           => "The input <code>%value%</code> is not greater than <strong>'%min%'</strong>.",
		self::NOT_GREATER_INCLUSIVE => "The input <code>%value%</code> is not greater or equal than <strong>'%min%'</strong>."
	];

	/**
	 * @var array
	 */
	protected $options = [
		'inclusive' => FALSE,
		'min'       => 0,
	];

	/**
	 * {@inheritdoc}
	 */
	public function is_valid( $value ) {

		$inclusive = (bool) $this->options[ 'inclusive' ];
		$min       = $this->options[ 'min' ];

		if ( $inclusive ) {
			if ( $min > $value ) {
				$this->set_error_message( self::NOT_GREATER_INCLUSIVE, $value );

				return FALSE;
			}
		} else {
			if ( $min >= $value ) {
				$this->set_error_message( self::NOT_GREATER, $value );

				return FALSE;
			}
		}

		return TRUE;
	}

}