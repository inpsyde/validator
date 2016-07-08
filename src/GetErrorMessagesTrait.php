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
 * `ValidatorInterface::get_error_messages()` is deprecated, but not removed yet for backward compatibility.
 * It means al the validators needs to implement that method for now.
 * To avoid repeat the same code again and again, we use this trait, that will be removed when
 * `ValidatorInterface::get_error_messages()` will be removed, so avoid use for custom validators.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package inpsyde-validator
 */
trait GetErrorMessagesTrait {

	/**
	 * @see ValidatorInterface::get_error_messages()
	 * @see ExtendedValidatorInterface::get_error_code()
	 * @see ErrorLogger::log_error()
	 * @see ErrorLogger::get_last_message()
	 * @return array
	 */
	public function get_error_messages() {

		if ( ! $this instanceof ExtendedValidatorInterface ) {
			return [ ];
		}

		if ( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || ! defined( 'WP_DEBUG' ) ) {
			trigger_error(
				sprintf(
					'%s::%s() is deprecated. Please use `Validator\DataValidator` or `Error\ErrorLogger`.',
					get_class( $this ),
					__FUNCTION__
				),
				E_USER_DEPRECATED
			);
		}

		/** @var ExtendedValidatorInterface $this */

		$logger = ( new Error\ErrorLogger() )
			->log_error( $this );

		return [ $logger->get_last_message( $this->get_error_code() ) ];

	}
}