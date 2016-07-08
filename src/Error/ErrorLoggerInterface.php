<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the inpsyde-validator package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inpsyde\Validator\Error;

use Inpsyde\Validator\ExtendedValidatorInterface;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package inpsyde-validator
 */
interface ErrorLoggerInterface extends \Countable, \IteratorAggregate {

	const INVALID_DATE = 'invalid_date';
	const INVALID_DATE_FORMAT = 'invalid_date_format';
	const INVALID_DNS = 'invalid_dns';
	const INVALID_TYPE_NON_ARRAY = 'invalid_type_non_array';
	const INVALID_TYPE_NON_DATE = 'invalid_type_non_date';
	const INVALID_TYPE_NON_NUMERIC = 'invalid_type_non_numeric';
	const INVALID_TYPE_NON_SCALAR = 'invalid_type_non_scalar';
	const INVALID_TYPE_NON_STRING = 'invalid_type_non_string';
	const INVALID_TYPE_NON_TRAVERSABLE = 'invalid_type_non_traversable';
	const IS_EMPTY = 'is_empty';
	const NOT_BETWEEN = 'not_between';
	const NOT_BETWEEN_STRICT = 'not_between_strict';
	const NOT_GREATER = 'not_greater_than';
	const NOT_GREATER_INCLUSIVE = 'not_greater_than_inclusive';
	const NOT_IN_ARRAY = 'not_in_array';
	const NOT_LESS = 'not_less_than';
	const NOT_LESS_INCLUSIVE = 'not_less_than_inclusive';
	const NOT_MATCH = 'not_match';
	const NOT_URL = 'not_url';
	const MULTIPLE_ERRORS = 'multiple_errors';
	const REGEX_INTERNAL_ERROR = 'regex_internal';

	/**
	 * Logs an error.
	 *
	 * If no custom message is provided, a default one have to be used.
	 *
	 * @param ExtendedValidatorInterface $validator
	 * @param string|null                $error_template
	 *
	 * @return ErrorLoggerInterface Implements fluent interface
	 *
	 * @internal param string $error_code One of the interface constants.
	 * @internal param array $error_data
	 */
	public function log_error( ExtendedValidatorInterface $validator, $error_template = NULL );

	/**
	 * Returns an array of logged error messages.
	 * If no code is provided, must return all messages, otherwise just the messages for the given code.
	 * Return empty array if no error was logged at all, or if no error was logged for given code.
	 *
	 * @param string|null $error_code
	 *
	 * @return \string[]
	 *
	 * @throws \InvalidArgumentException If given error code is provided and invalid.
	 */
	public function get_error_messages( $error_code = NULL );

	/**
	 *  Returns an array of all message codes logged. Empty array if no error was logged.
	 *
	 * @return string[]
	 */
	public function get_error_codes();

	/**
	 * Returns last error message logged for given code.
	 *
	 * If no code is given, should return last error message.
	 * Return empty string if no error was logged at all, or if no error was logged for given code.
	 *
	 * @param string|null $error_code
	 *
	 * @return string
	 *
	 * @throws \InvalidArgumentException If given error code is provided and invalid.
	 */
	public function get_last_message( $error_code = NULL );

	/**
	 * Set a default message for a given error code.
	 *
	 * Useful to set a custom default message for all errors of a specific code.
	 *
	 * @param string $error_code
	 * @param string $error_template
	 *
	 * @return ErrorLoggerInterface Implements fluent interface
	 *
	 * @throws \InvalidArgumentException If given error code is invalid or custom message is not a string.
	 */
	public function use_error_template( $error_code, $error_template );

}