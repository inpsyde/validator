<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the inpsyde-validator package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inpsyde\Validator\Tests\Stub;

use Inpsyde\Validator\AbstractValidator;

/**
 * Class AlwaysFalseWithInvalidMessageValidator
 *
 * This class is just a simple "Fake" which is only used in tests to check, if the $options and error messages can be
 * overwritten.
 *
 * @author     Christian Brückner <chris@chrico.info>
 * @package    inpsyde-validator
 * @license    http://opensource.org/licenses/MIT MIT
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