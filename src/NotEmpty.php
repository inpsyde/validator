<?php

namespace Inpsyde\Validator;

/**
 * Class NotEmpty
 *
 * @package Inpsyde\Validator
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