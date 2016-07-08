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
 * Interface MapValidatorInterface
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
interface ErrorLoggerAwareValidatorInterface {

	/**
	 * Return an instance of `ErrorLoggerAwareValidatorInterface` that make use of given error logger instance.
	 * The method should be implemented in a way that keeps the object immutable.
	 *
	 * @param Error\ErrorLoggerInterface $error_logger
	 *
	 * @return ErrorLoggerAwareValidatorInterface
	 */
	public function with_error_logger( Error\ErrorLoggerInterface $error_logger );

	/**
	 * Return a plain array of error messages.
	 *
	 * @return string[]
	 */
	public function get_error_messages();

}