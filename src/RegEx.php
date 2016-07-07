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
 * Class RegEx
 *
 * @author  Christian Brückner <chris@chrico.info>
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
class RegEx implements ErrorAwareValidatorInterface {

	use ValidatorDataGetterTrait;
	use GetErrorMessagesTrait;

	/**
	 * @deprecated Error codes are now defined in Error\ErrorLoggerInterface
	 */
	const INVALID_TYPE = Error\ErrorLoggerInterface::INVALID_TYPE_NON_SCALAR;

	/**
	 * @deprecated Error codes are now defined in Error\ErrorLoggerInterface
	 */
	const NOT_MATCH = Error\ErrorLoggerInterface::NOT_MATCH;

	/**
	 * @deprecated Error codes are now defined in Error\ErrorLoggerInterface
	 */
	const ERROROUS = Error\ErrorLoggerInterface::REGEX_INTERNAL_ERROR;

	/**
	 * @var array
	 */
	protected $options = [ ];

	/**
	 * @var array
	 * @deprecated
	 */
	protected $message_templates = [
		Error\ErrorLoggerInterface::INVALID_TYPE_NON_SCALAR => "Invalid type given. String, integer or float expected",
		Error\ErrorLoggerInterface::NOT_MATCH               => "The input does not match against pattern '%pattern%'",
		Error\ErrorLoggerInterface::REGEX_INTERNAL_ERROR    => "There was an internal error while using the pattern '%pattern%'",
	];

	/**
	 * @param array $options
	 */
	public function __construct( array $options = [ ] ) {

		$pattern = isset( $options[ 'pattern' ] ) && is_string( $options[ 'pattern' ] ) ? $options[ 'pattern' ] : '';
		$first   = $pattern ? substr( $this->options[ 'pattern' ], 0, 1 ) : '';
		$last    = $pattern ? substr( $this->options[ 'pattern' ], - 1, 1 ) : '';

		if ( $first && ( $first !== $last || strlen( $this->options[ 'pattern' ] ) === 1 ) ) {
			$pattern = "~{$pattern}~";
		}

		$this->options[ 'pattern' ]  = $pattern;
		$this->input_data            = $this->options;
		$this->input_data[ 'value' ] = NULL;
	}

	/**
	 * @inheritdoc
	 */
	public function is_valid( $value ) {

		$this->input_data[ 'value' ] = $value;

		$pattern = $this->options[ 'pattern' ];

		if ( ! is_string( $value ) && ! is_int( $value ) && ! is_float( $value ) ) {
			$this->error_code = Error\ErrorLoggerInterface::INVALID_TYPE_NON_SCALAR;

			return FALSE;
		}

		$status = @preg_match( $pattern, $value );

		if ( $status === FALSE ) {
			$this->error_code = Error\ErrorLoggerInterface::REGEX_INTERNAL_ERROR;

			return FALSE;
		}

		$status or $this->error_code = Error\ErrorLoggerInterface::NOT_MATCH;

		return $status > 0;
	}

}