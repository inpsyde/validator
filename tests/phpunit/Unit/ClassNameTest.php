<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the inpsyde-validator package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inpsyde\Validator\Tests\Unit;

use Inpsyde\Validator\ClassName;
use Inpsyde\Validator\Error\ErrorLoggerInterface;

/**
 * Class ClassNameTest
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
class ClassNameTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @param mixed $value
	 * @param bool  $expected_valid
	 * @param       $message
	 *
	 * @dataProvider is_valid_cases_provider
	 */
	public function test_is_valid( $value, $expected_valid, $message ) {

		$bulk = new ClassName();

		$result = $bulk->is_valid( $value );
		$expected_valid ? $this->assertTrue( $result, $message ) : $this->assertFalse( $result, $message );
	}

	public function is_valid_cases_provider() {

		return [
			[ \ArrayAccess::class, FALSE, '"ArrayAccess" is an interface name' ],
			[ \ArrayObject::class, TRUE, '"ArrayObject" is a class name' ],
			[ new \ArrayObject(), FALSE, 'ArrayObject object is not a class name' ],
			[ '_FOO_', FALSE, '_FOO_ is not a class name' ],
			[ __CLASS__, TRUE, __CLASS__ . ' is a class name' ],
		];
	}

	public function test_specific_error_for_non_strings() {

		$bulk = new ClassName();

		$this->assertFalse( $bulk->is_valid( TRUE ) );
		$this->assertSame( ErrorLoggerInterface::INVALID_TYPE_NON_STRING, $bulk->get_error_code() );

	}

}