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
 * Class Between
 *
 * @author  Christian Brückner <chris@chrico.info>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
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