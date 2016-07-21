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
 * Class Type
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
class Type implements ExtendedValidatorInterface {

	use ValidatorDataGetterTrait;
	use GetErrorMessagesTrait;

	private static $types = [
		'integer'     => 'integer',
		'int'         => 'integer',
		'double'      => 'double',
		'float'       => 'double',
		'string'      => 'string',
		'int'         => 'integer',
		'boolean'     => 'boolean',
		'bool'        => 'boolean',
		'resource'    => 'resource',
		'object'      => 'object',
		'null'        => 'null',
		'traversable' => 'traversable',
		'numeric'     => 'numeric',
		'number'      => 'numeric',
	];

	/**
	 * @var array
	 */
	protected $options = [ ];

	/**
	 * @param array $options
	 */
	public function __construct( array $options = [ ] ) {

		if ( empty( $options[ 'type' ] ) || ! is_string( $options[ 'type' ] ) ) {
			throw new \InvalidArgumentException( sprintf( '%s "type" option must be in a string.', __CLASS__ ) );
		}

		$type  = $options[ 'type' ];
		$lower = strtolower( $type );

		$this->options[ 'type' ]     = array_key_exists( $lower, self::$types ) ? self::$types[ $lower ] : $type;
		$this->input_data            = $this->options;
		$this->input_data[ 'value' ] = NULL;
	}

	/**
	 * @inheritdoc
	 */
	public function is_valid( $value ) {

		$this->input_data = [ 'value' => $value ];

		if ( strtolower( gettype( $value ) ) === $this->options[ 'type' ] ) {
			return TRUE;
		}

		if ( is_object( $value ) && is_a( $value, $this->options[ 'type' ] ) ) {
			return TRUE;
		}

		if ( $this->options[ 'type' ] === 'numeric' && is_numeric( $value ) ) {
			return TRUE;
		}

		if ( $this->options[ 'type' ] === 'traversable' && ( is_array( $value ) || $value instanceof \Traversable ) ) {
			return TRUE;
		}

		$this->error_code = Error\ErrorLoggerInterface::INVALID_TYPE_GIVEN;
		$this->update_error_messages();

		return FALSE;
	}

}