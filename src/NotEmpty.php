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
 * Class NotEmpty
 *
 * @author  Christian Brückner <chris@chrico.info>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
class NotEmpty extends AbstractValidator {

	const IS_EMPTY = 'isEmpty';

	/**
	 * {@inheritdoc}
	 */
	protected $message_templates = [
		self::IS_EMPTY => "This value should not be empty.",
	];

	/**
	 * {@inheritdoc}
	 */
	public function is_valid( $value ) {

		if ( $value === FALSE || ( empty( $value ) && $value != '0' ) ) {
			$this->set_error_message( self::IS_EMPTY, $value );

			return FALSE;
		}

		return TRUE;
	}

}