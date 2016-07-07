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
 * Interface ValidatorInterface
 *
 * @author  Christian Brückner <chris@chrico.info>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
interface ValidatorInterface {

	/**
	 * Returns true if and only if $value meets the validation requirements
	 *
	 * If $value fails validation, then this method returns false, and
	 * get_error_messages() will return an array of messages that explain why the
	 * validation failed.
	 *
	 * @param   mixed $value
	 *
	 * @return  bool $is_valid
	 */
	public function is_valid( $value );

	/**
	 * Returns a messages that explain why the most recent is_valid()
	 * call returned false.
	 *
	 * If is_valid() was never called or if the most recent is_valid() call
	 * returned true, then this method returns an empty array.
	 *
	 * @return  array
	 */
	public function get_error_messages();

}