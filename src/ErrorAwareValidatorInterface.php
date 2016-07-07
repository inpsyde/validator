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
 * The unique method of this interface should go in the `ValidatorInterface`, however, for backward compatibility
 * we can't just add a method to existing interface, or any custom validator out there will immediately break.
 * In next major release, we can deprecate this interface and move `get_error_code()` to `ValidatorInterface`.
 * Finally, a major release after, we can remove this interface for good.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
interface ErrorAwareValidatorInterface extends ValidatorInterface {

	/**
	 * Return the error code
	 *
	 * @return string
	 */
	public function get_error_code();

}