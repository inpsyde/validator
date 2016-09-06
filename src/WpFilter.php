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
 * Class WpFilter
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
class WpFilter implements ExtendedValidatorInterface {

	use ValidatorDataGetterTrait;
	use GetErrorMessagesTrait;

	/**
	 * @param array $options
	 */
	public function __construct( array $options = [ ] ) {

		if ( empty( $options[ 'filter' ] ) || ! is_string( $options[ 'filter' ] ) ) {
			throw new \InvalidArgumentException( sprintf( '%s "filter" option must be in a string.', __CLASS__ ) );
		}

		if ( ! function_exists( 'apply_filters' ) ) {
			throw new \InvalidArgumentException( sprintf( '%s can only be used in WordPress context.', __CLASS__ ) );
		}

		$this->input_data            = $options;
		$this->input_data[ 'value' ] = NULL;
	}

	/**
	 * @inheritdoc
	 */
	public function is_valid( $value ) {

		$this->input_data[ 'value' ] = $value;

		$valid = apply_filters( $this->input_data[ 'filter' ], $value );
		$valid = filter_var( $valid, FILTER_VALIDATE_BOOLEAN );

		if ( ! $valid ) {
			$this->error_code = ErrorLoggerInterface::WP_FILTER_ERROR;
			$this->update_error_messages();
		}

		return $valid;
	}

}