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
	 * @var array
	 */
	protected $options = [ ];

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

		$this->options[ 'filter' ]     = $options[ 'filter' ];
		$this->options[ 'error_code' ] = empty( $options[ 'error_code' ] ) || ! is_string( $options[ 'error_code' ] )
			? ErrorLoggerInterface::CUSTOM_ERROR
			: $options[ 'error_code' ];

		$this->input_data            = $this->options;
		$this->input_data[ 'value' ] = NULL;
	}

	/**
	 * @inheritdoc
	 */
	public function is_valid( $value ) {

		$this->input_data = [ 'value' => $value ];

		$valid = apply_filters( $this->options[ 'filter' ], $value );

		if ( ! filter_var( $valid, FILTER_VALIDATE_BOOLEAN ) ) {
			$this->error_code = $this->options[ 'error_code' ];
			$this->update_error_messages();
		}

		return $valid;
	}

}