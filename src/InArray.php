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
 * Class InArray
 *
 * @author  Christian Brückner <chris@chrico.info>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
class InArray extends AbstractValidator {

	const NOT_IN_ARRAY = 'notInArray';

	/**
	 * @var array
	 */
	protected $message_templates = [
		self::NOT_IN_ARRAY => "The input <code>%value%</code> is not in the haystack: <code>%haystack%</code>.",
	];

	/**
	 * @var array
	 */
	protected $options = [
		'strict'   => TRUE,
		'haystack' => [ ]
	];

	/**
	 * {@inheritdoc}
	 */
	public function is_valid( $value ) {

		$strict   = (bool) $this->options[ 'strict' ];
		$haystack = $this->options[ 'haystack' ];

		if ( ! in_array( $value, $haystack, $strict ) ) {
			$this->set_error_message( self::NOT_IN_ARRAY, $value );

			return FALSE;
		}

		return TRUE;
	}

}
