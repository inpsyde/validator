<?php
namespace Inpsyde\Validator;

/**
 * Class LessThan
 *
 * @package Inpsyde\Validator
 */
class LessThan extends AbstractValidator {

	const NOT_LESS = 'notLessThan';
	const NOT_LESS_INCLUSIVE = 'notLessThanInclusive';

	/**
	 * @var array
	 */
	protected $message_templates = [
		self::NOT_LESS           => "The input <code>%value%</code> is not less than <strong>'%max%'</strong>.",
		self::NOT_LESS_INCLUSIVE => "The input <code>%value%</code> is not less or equal than <strong>'%max%'</strong>."
	];

	/**
	 * @var array
	 */
	protected $options = [
		'inclusive' => FALSE,
		'max'       => 0,
	];

	/**
	 * {@inheritdoc}
	 */
	public function is_valid( $value ) {

		$inclusive = (bool) $this->options[ 'inclusive' ];
		$max       = $this->options[ 'max' ];

		if ( $inclusive ) {
			if ( $value > $max ) {
				$this->set_error_message( self::NOT_LESS_INCLUSIVE, $value );

				return FALSE;
			}
		} else {
			if ( $value >= $max ) {
				$this->set_error_message( self::NOT_LESS, $value );

				return FALSE;
			}
		}

		return TRUE;
	}

}