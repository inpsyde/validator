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
 * Interface MultiValidatorInterface
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
interface MultiValidatorInterface extends ValidatorInterface, \Countable {

	/**
	 * Adds a "leaf" validator to the stack.
	 *
	 * @param ExtendedValidatorInterface $validator
	 *
	 * @return MultiValidatorInterface
	 */
	public function add_validator( ExtendedValidatorInterface $validator );

	/**
	 * Returns and array of occurred error codes
	 *
	 * @return string[]
	 */
	public function get_error_codes();

	/**
	 * Returns and array of occurred error data.
	 * If a code is provided, returns only data for given code.
	 * Return empty array if no error at all, or if no error occurred for given code.
	 *
	 * @param null $error_code
	 *
	 * @return array[]
	 */
	public function get_error_data( $error_code = null );

}