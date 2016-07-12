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
	 */
	protected $options = [ ];

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
		$this->options[ 'inclusive' ] = isset( $options[ 'inclusive' ] )
			? filter_var( $options[ 'inclusive' ], FILTER_VALIDATE_BOOLEAN )
			: TRUE;

		$this->options[ 'min' ] = isset( $options[ 'min' ] ) ? $options[ 'min' ] : 0;
		$this->options[ 'max' ] = isset( $options[ 'max' ] ) ? $options[ 'max' ] : PHP_INT_MAX;

		$this->input_data            = $this->options;
		$this->input_data[ 'value' ] = NULL;
	}

	/**
	 * @inheritdoc
	 */
	public function is_valid( $value ) {

		$this->input_data[ 'value' ] = $value;
		$inc                         = $this->options[ 'inclusive' ];
		$ok                          = $inc ? $value >= $this->options[ 'min' ] : $value > $this->options[ 'min' ];
		$ok and $ok = $inc ? $value <= $this->options[ 'max' ] : $value < $this->options[ 'max' ];
		$ok or $this->error_code = $inc ? ErrorLoggerInterface::NOT_BETWEEN : ErrorLoggerInterface::NOT_BETWEEN_STRICT;
		$ok or $this->update_error_messages();

		return $ok;
	}
}