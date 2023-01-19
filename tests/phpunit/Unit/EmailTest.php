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

use Inpsyde\Validator\Error\ErrorLoggerInterface;
use Inpsyde\Validator\Email;

/**
 * Class EmailTest
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
class EmailTest extends AbstractTestCase {

	/**
	 * Ensures that the validator follows expected behavior
	 *
	 * @dataProvider provide_is_valid_cases
	 *
	 * @param string $input
	 * @param bool   $expect
	 */
	public function test_is_valid( $input, $expect ) {

		$validator = new Email();

		$expect
			? $this->assertTrue( $validator->is_valid( $input ), "Error for {$input}" )
			: $this->assertFalse( $validator->is_valid( $input ), "Error for {$input}" );
	}

	/**
	 * @return array
	 */
	public function provide_is_valid_cases() {

		return [
			// $input, $expect
			[ 'http://www.example.com', FALSE ],
			[ 'www.example.com', FALSE ],
			[ 'test@www.example.com', TRUE ],
			[ 'test@www@example.com', FALSE ],
			[ 'test@', FALSE ],
			[ 'test@example', FALSE ],
			[ 'test@example.', FALSE ],
			[ 'test@.example', FALSE ],
			[ '@example.com', FALSE ],
			[ '!#$%&`*+/=?^`{|}~@example.com', TRUE ],
			[ 'test\@test@iana.org@example.com', FALSE ],
			[ 'test@255.255.255.255', FALSE ]
		];
	}

	/**
	 * Tests that error code is returned according to validation results and options.
	 */
	public function test_get_error_code() {

		$validator = new Email( [ 'check_dns' => TRUE ] );

		$validator->is_valid( new \stdClass() );
		$code_non_string = $validator->get_error_code();

		$validator->is_valid( '' );
		$code_empty = $validator->get_error_code();

		$validator->is_valid( 'foo' );
		$code_non_email = $validator->get_error_code();

		$validator->is_valid( 'foo@dsasfsdfsdfdsf.mehmehe' );
		$code_no_dns = $validator->get_error_code();

		$this->assertSame( ErrorLoggerInterface::INVALID_TYPE_NON_STRING, $code_non_string );
		$this->assertSame( ErrorLoggerInterface::IS_EMPTY, $code_empty );
		$this->assertSame( ErrorLoggerInterface::NOT_EMAIL, $code_non_email );
		$this->assertSame( ErrorLoggerInterface::INVALID_DNS, $code_no_dns );
	}

	/**
	 * Tests that input data is returned according to validation results and options.
	 */
	public function test_get_input_data() {

		$validator = new Email();

		$validator->is_valid( 'info@example.com' );
		$input = $validator->get_input_data();

		$this->assertIsArray( $input );
		$this->assertArrayHasKey( 'value', $input );
		$this->assertSame( 'info@example.com', $input[ 'value' ] );

		$validator->is_valid( 'meh' );

		$input = $validator->get_input_data();
		$this->assertSame( 'meh', $input[ 'value' ] );
	}

}