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
 * Class InArray
 *
 * @author  Christian Brückner <chris@chrico.info>
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
class InArray implements ExtendedValidatorInterface {

	use ValidatorDataGetterTrait;
	use GetErrorMessagesTrait;

	/**
	 * @deprecated Error codes are now defined in Error\ErrorLoggerInterface
	 */
	const NOT_IN_ARRAY = Error\ErrorLoggerInterface::NOT_IN_ARRAY;

	/**
	 * @var array
	 * @deprecated
	 */
	protected $message_templates = [
		Error\ErrorLoggerInterface::NOT_IN_ARRAY => "The input <code>%value%</code> is not in the haystack: <code>%haystack%</code>.",
	];

	/**
	 * @param array $options
	 */
	public function __construct( array $options = [ ] ) {

		// Whether to do inclusive comparisons, allowing equivalence to min and/or max
		$options[ 'strict' ] = isset( $options[ 'strict' ] )
			? filter_var( $options[ 'strict' ], FILTER_VALIDATE_BOOLEAN )
			: TRUE;

		$options[ 'haystack' ] = isset( $options[ 'haystack' ] )
			? (array) $options[ 'haystack' ]
			: [ ];

		$this->input_data            = $options;
		$this->input_data[ 'value' ] = NULL;
	}

	/**
	 * @inheritdoc
	 */
	public function is_valid( $value ) {

		$this->input_data[ 'value' ] = $value;

		$valid = in_array( $value, $this->input_data[ 'haystack' ], $this->input_data[ 'strict' ] );
		$valid or $this->error_code = Error\ErrorLoggerInterface::NOT_IN_ARRAY;
		$valid or $this->update_error_messages();

		return $valid;
	}
}
