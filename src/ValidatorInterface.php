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
	 * Validate given value against some requirements.
	 *
	 * @param  mixed $value
	 *
	 * @return bool $is_valid `true` if and only if given value meets the validation requirements.
	 */
	public function is_valid( $value );

	/**
	 * @deprecated Messages are now managed via the `ErrorMessages` class.
	 *
	 * @return array
	 */
	public function get_error_messages();

}