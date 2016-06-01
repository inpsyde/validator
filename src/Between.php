<?php

namespace Inpsyde\Validator;

/**
 * Class Between
 *
 * @package Inpsyde\Validator
 */
class Between extends AbstractValidator {

	const NOT_BETWEEN = 'notBetween';
	const NOT_BETWEEN_STRICT = 'notBetweenStrict';

	/**
	 * @var array
	 */
	protected $message_templates = [
		self::NOT_BETWEEN        => "The input <code>%value%</code> is not between <strong>'%min%'</strong> and <strong>'%max%'</strong>, inclusively",
		self::NOT_BETWEEN_STRICT => "The input <code>%value%</code> is not strictly between <strong>'%min%'</strong> and <strong>'%max%'</strong>"
	];

	/**
	 * @var array
	 */
	protected $options = [
		'inclusive' => TRUE,  // Whether to do inclusive comparisons, allowing equivalence to min and/or max
		'min'       => 0,
		'max'       => PHP_INT_MAX,
	];

	/**
	 * {@inheritdoc}
	 */
	public function is_valid( $value ) {

		$inclusive = (bool) $this->options[ 'inclusive' ];
		$min       = $this->options[ 'min' ];
		$max       = $this->options[ 'max' ];

		if ( $inclusive ) {
			if ( $value < $min || $value > $max ) {
				$this->set_error_message( self::NOT_BETWEEN_STRICT, $value );

				return FALSE;
			}
		} else {
			if ( $value <= $min || $value >= $max ) {
				$this->set_error_message( self::NOT_BETWEEN, $value );

				return FALSE;
			}
		}

		return TRUE;
	}

}