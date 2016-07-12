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

use Inpsyde\Validator\Error\ErrorLoggerInterface;

/**
 * Class Callback
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
class Callback implements ExtendedValidatorInterface {

	use ValidatorDataGetterTrait;
	use GetErrorMessagesTrait;

	/**
	 * @var array
	 */
	protected $options = [ ];

	/**
	 * @param array $options
	 */
	public function __construct( array $options = [ ] ) {

		if ( empty( $options[ 'callback' ] ) || ! is_callable( $options[ 'callback' ] ) ) {
			throw new \InvalidArgumentException( sprintf( '%s "callback" option must be callable.', __CLASS__ ) );
		}

		$this->options[ 'callback' ]   = $options[ 'callback' ];
		$this->options[ 'error_code' ] = ( empty( $options[ 'error_code' ] )
			|| ! is_string(
				$options[ 'error_code' ]
			) )
			? ErrorLoggerInterface::CUSTOM_ERROR
			: $options[ 'error_code' ];
	}

	/**
	 * @inheritdoc
	 */
	public function is_valid( $value ) {

		$this->input_data = [ 'value' => $value ];

		/** @var callable $callback */
		$callback = $this->options[ 'callback' ];

		$valid = $callback( $value );

		if ( ! $valid ) {
			$this->error_code = $this->options[ 'error_code' ];
			$this->update_error_messages();
		}

		return $valid;
	}

}