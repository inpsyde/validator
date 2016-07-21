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
trait MultiValidatorDataGetterTrait {

	/**
	 * @var array
	 */
	private $error_data = [ ];

	/**
	 * @inheritdoc
	 */
	public function get_error_codes() {

		return $this->error_data ? array_keys( $this->error_data ) : [ ];
	}

	/**
	 * @inheritdoc
	 */
	public function get_error_data( $error_code = NULL ) {

		if ( is_null( $error_code ) ) {
			return $this->error_data;
		} elseif ( ! array_key_exists( $error_code, $this->error_data ) ) {
			return [ ];
		}

		return $this->error_data[ $error_code ];
	}
}