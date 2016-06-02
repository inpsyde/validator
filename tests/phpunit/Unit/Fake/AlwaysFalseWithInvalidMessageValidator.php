<?php

namespace Inpsyde\Validator\Tests\Unit\Fake;

use Inpsyde\Validator\AbstractValidator;

class AlwaysFalseWithInvalidMessageValidator extends AbstractValidator {

	const INVALID = 'invalid';
	
	protected $message_templates = [
		self::INVALID => 'value: %value% and option "key" => %key%'
	];

	protected $options = [
		'key' => 'value'
	];

	/**
	 * Returns true if and only if $value meets the validation requirements
	 *
	 * If $value fails validation, then this method returns false, and
	 * get_error_messages() will return an array of messages that explain why the
	 * validation failed.
	 *
	 * @param   mixed $value
	 *
	 * @return  bool $is_valid
	 */
	public function is_valid( $value ) {

		$this->set_error_message( 'invalid', $value );

		return FALSE;
	}
}