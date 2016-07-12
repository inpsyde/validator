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
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package inpsyde-validator
 */
trait ValidatorDataGetterTrait {

	/**
	 * @var array
	 */
	protected $input_data = [ ];

	/**
	 * @var string
	 */
	protected $error_code = '';

	/**
	 * @see ExtendedValidatorInterface::get_error_code()
	 */
	public function get_error_code() {

		return $this->error_code;
	}

	/**
	 * @see ExtendedValidatorInterface::get_input_data()
	 */
	public function get_input_data() {

		return $this->input_data;
	}
}