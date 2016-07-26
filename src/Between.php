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

use Inpsyde\Validator\Error\ErrorLoggerInterface;

/**
 * Class Between
 *
 * @author  Christian Brückner <chris@chrico.info>
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
class Between implements ExtendedValidatorInterface {

	use ValidatorDataGetterTrait;
	use GetErrorMessagesTrait;

	/**
	 * @deprecated Error codes are now defined in Error\ErrorLoggerInterface
	 */
	const NOT_BETWEEN = Error\ErrorLoggerInterface::NOT_BETWEEN;

	/**
	 * @deprecated Error codes are now defined in Error\ErrorLoggerInterface
	 */
	const NOT_BETWEEN_STRICT = Error\ErrorLoggerInterface::NOT_BETWEEN_STRICT;

	/**
	 * @var array
	 * @deprecated
	 */
	protected $message_templates = [
		Error\ErrorLoggerInterface::NOT_BETWEEN        => "The input <code>%value%</code> is not between <strong>'%min%'</strong> and <strong>'%max%'</strong>, inclusively",
		Error\ErrorLoggerInterface::NOT_BETWEEN_STRICT => "The input <code>%value%</code> is not strictly between <strong>'%min%'</strong> and <strong>'%max%'</strong>"
	];

	public function __construct( array $options = [ ] ) {

		// Whether to do inclusive comparisons, allowing equivalence to min and/or max
		$options[ 'inclusive' ] = isset( $options[ 'inclusive' ] )
			? filter_var( $options[ 'inclusive' ], FILTER_VALIDATE_BOOLEAN )
			: TRUE;

		$options[ 'min' ] = isset( $options[ 'min' ] ) ? $options[ 'min' ] : 0;
		$options[ 'max' ] = isset( $options[ 'max' ] ) ? $options[ 'max' ] : PHP_INT_MAX;

		$this->input_data            = $options;
		$this->input_data[ 'value' ] = NULL;
	}

	/**
	 * @inheritdoc
	 */
	public function is_valid( $value ) {

		$this->input_data[ 'value' ] = $value;
		$inc                         = $this->input_data[ 'inclusive' ];
		$ok                          = $inc ? $value >= $this->input_data[ 'min' ] : $value > $this->input_data[ 'min' ];
		$ok and $ok = $inc ? $value <= $this->input_data[ 'max' ] : $value < $this->input_data[ 'max' ];
		$ok or $this->error_code = $inc ? ErrorLoggerInterface::NOT_BETWEEN : ErrorLoggerInterface::NOT_BETWEEN_STRICT;
		$ok or $this->update_error_messages();

		return $ok;
	}
}