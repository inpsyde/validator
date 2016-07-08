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
 * Class Url
 *
 * @author  Christian Brückner <chris@chrico.info>
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
class Url implements ExtendedValidatorInterface {

	use ValidatorDataGetterTrait;
	use GetErrorMessagesTrait;

	/**
	 * @deprecated Error codes are now defined in Error\ErrorLoggerInterface
	 */
	const NOT_URL = Error\ErrorLoggerInterface::NOT_URL;

	/**
	 * @deprecated Error codes are now defined in Error\ErrorLoggerInterface
	 */
	const INVALID_TYPE = Error\ErrorLoggerInterface::INVALID_TYPE_NON_STRING;

	/**
	 * @deprecated Error codes are now defined in Error\ErrorLoggerInterface
	 */
	const INVALID_DNS = Error\ErrorLoggerInterface::INVALID_DNS;

	/**
	 * @deprecated Error codes are now defined in Error\ErrorLoggerInterface
	 */
	const NOT_EMPTY = Error\ErrorLoggerInterface::IS_EMPTY;

	/**
	 * The pattern to validate the given value as "url".
	 *
	 */
	const PATTERN = '~^
            (%s)://                                 # protocol
            (([\pL\pN-]+:)?([\pL\pN-]+)@)?          # basic auth
            (
                ([\pL\pN\pS-\.])+(\.?([\pL]|xn\-\-[\pL\pN-]+)+\.?) # a domain name
                    |                                              # or
                \d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}                 # a IP address
                    |                                              # or
                \[
                    (?:(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){6})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:::(?:(?:(?:[0-9a-f]{1,4})):){5})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){4})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,1}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){3})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,2}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){2})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,3}(?:(?:[0-9a-f]{1,4})))?::(?:(?:[0-9a-f]{1,4})):)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,4}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,5}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,6}(?:(?:[0-9a-f]{1,4})))?::))))
                \]  # a IPv6 address
            )
            (:[0-9]+)?                              # a port (optional)
            (/?|/\S+|\?\S*|\#\S*)                   # a /, nothing, a / with something, a query or a fragment
        $~ixu';

	/**
	 * @var array
	 */
	protected $options = [
		'allowed_protocols' => [ 'http', 'https' ],
		'check_dns'         => FALSE
	];

	/**
	 * @var array
	 */
	protected $message_templates = [
		Error\ErrorLoggerInterface::NOT_URL                 => "The input <code>%value%</code> is not a valid URL.",
		Error\ErrorLoggerInterface::INVALID_TYPE_NON_STRING => "The input <code>%value%</code> should be a string.",
		Error\ErrorLoggerInterface::INVALID_DNS             => "The host for the given input <code>%value%</code> could not be resolved.",
		Error\ErrorLoggerInterface::IS_EMPTY                => "The given input shouldn't be empty."
	];

	/**
	 * @param array $options
	 */
	public function __construct( array $options = [ ] ) {

		$protocols = isset( $options[ 'allowed_protocols' ] ) && is_array( $options[ 'allowed_protocols' ] )
			? array_filter( $options[ 'allowed_protocols' ], 'is_string ' )
			: [ ];

		$this->options[ 'allowed_protocols' ] = $protocols ? : [ 'http', 'https' ];

		$this->options[ 'check_dns' ] = isset( $options[ 'check_dns' ] )
			? filter_var( $options[ 'check_dns' ], FILTER_VALIDATE_BOOLEAN )
			: FALSE;

		$this->input_data            = $this->options;
		$this->input_data[ 'value' ] = NULL;
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_valid( $value ) {

		$this->input_data[ 'value' ] = $value;

		( is_object( $value ) && method_exists( $value, '__toString' ) ) and $value = (string) $value;

		if ( ! is_string( $value ) ) {
			$this->error_code = Error\ErrorLoggerInterface::INVALID_TYPE_NON_STRING;

			return FALSE;

		}

		if ( $value === '' ) {
			$this->error_code = Error\ErrorLoggerInterface::IS_EMPTY;

			return FALSE;
		}

		$pattern = sprintf( self::PATTERN, implode( '|', $this->options[ 'allowed_protocols' ] ) );
		if ( ! preg_match( $pattern, $value ) ) {
			$this->error_code = Error\ErrorLoggerInterface::NOT_URL;

			return FALSE;
		}

		if ( ! $this->options[ 'check_dns' ] ) {
			return TRUE;
		}

		$host  = parse_url( $value, PHP_URL_HOST );
		$valid = $host && checkdnsrr( $host, 'ANY' );
		$valid or $this->error_code = Error\ErrorLoggerInterface::INVALID_DNS;
		$valid or $this->update_error_messages();

		return $valid;
	}

}