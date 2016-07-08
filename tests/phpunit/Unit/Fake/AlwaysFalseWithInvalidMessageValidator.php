<?php

namespace Inpsyde\Validator\Tests\Unit\Fake;

use Inpsyde\Validator\AbstractValidator;

/**
 * Class AlwaysFalseWithInvalidMessageValidator
 *
 * This class is just a simple "Fake" which is only used in tests to check, if the $options and error messages can be
 * overwritten.
 *
 * @package Inpsyde\Validator\Tests\Unit\Fake
 */
class AlwaysFalseWithInvalidMessageValidator extends AbstractValidator {

	const INVALID = 'invalid';

	protected $message_templates = [
		self::INVALID => 'value: %value% and option "key" => %key%'
	];

	protected $options = [
		'key' => 'value'
	];

	/**
	 * Always return false.
	 */
	public function is_valid( $value ) {

		$this->set_error_message( 'invalid', $value );

		return FALSE;
	}
}