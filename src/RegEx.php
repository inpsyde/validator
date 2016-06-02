<?php

namespace Inpsyde\Validator;

/**
 * Class RegEx
 *
 * @package Inpsyde\Validator
 */
class RegEx extends AbstractValidator {

	const INVALID_TYPE = 'invalidType';
	const NOT_MATCH = 'notMatch';
	const ERROROUS = 'errorous';

	/**
	 * @var array
	 */
	protected $message_templates = [
		self::INVALID_TYPE => "Invalid type given. String, integer or float expected",
		self::NOT_MATCH    => "The input does not match against pattern '%pattern%'",
		self::ERROROUS     => "There was an internal error while using the pattern '%pattern%'",
	];

	/**
	 * @var array
	 */
	protected $options = [
		'pattern' => ''
	];

	/**
	 * {@inheritdoc}
	 */
	public function is_valid( $value ) {

		$pattern = $this->options[ 'pattern' ];

		if ( ! is_string( $value ) && ! is_int( $value ) && ! is_float( $value ) ) {
			$this->set_error_message( self::INVALID_TYPE, $value );

			return FALSE;
		}

		$status = preg_match( $pattern, $value );

		if ( $status === FALSE ) {
			$this->set_error_message( self::ERROROUS, $value );

			return FALSE;
		}

		if ( ! $status ) {
			$this->set_error_message( self::NOT_MATCH, $value );

			return FALSE;
		}

		return TRUE;
	}

}