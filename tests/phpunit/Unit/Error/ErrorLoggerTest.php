<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the inpsyde-validator package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inpsyde\Validator\Tests\Unit\Error;

use Inpsyde\Validator\Error\ErrorLogger;
use PHPUnit\Framework\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package inpsyde-validator
 */
class ErrorLoggerTest extends TestCase {

	/**
	 * Test that is possible to override specific messages via constructor
	 */
	public function test_message_override_in_constructor() {

		$logger          = new ErrorLogger();
		$logger_override = new ErrorLogger( [ ErrorLogger::IS_EMPTY => 'Hello man, I am empty.' ] );

		$default   = $this->get_messages( $logger );
		$overriden = $this->get_messages( $logger_override );

		$this->assertIsArray( $default );
		$this->assertIsArray( $overriden );
		$this->assertArrayHasKey( ErrorLogger::IS_EMPTY, $default );
		$this->assertArrayHasKey( ErrorLogger::IS_EMPTY, $overriden );
		$this->assertNotEquals( $default[ ErrorLogger::IS_EMPTY ], $overriden[ ErrorLogger::IS_EMPTY ] );
		$this->assertSame( 'Hello man, I am empty.', $overriden[ ErrorLogger::IS_EMPTY ] );
		$this->assertSame( $default[ ErrorLogger::INVALID_DATE ], $overriden[ ErrorLogger::INVALID_DATE ] );
	}

	public function test_log_error_set_messages_and_codes() {

		$logger = new ErrorLogger();

		$logged1        = $logger->log_error( ErrorLogger::IS_EMPTY );
		$logged2        = $logger->log_error( ErrorLogger::INVALID_TYPE_NON_STRING );
		$messages       = $logger->get_error_messages();
		$messages_empty = $logger->get_error_messages( ErrorLogger::IS_EMPTY );
		$codes          = $logger->get_error_codes();
		$last_message   = $logger->get_last_message();

		$this->assertSame( $logged1, $logger );
		$this->assertSame( $logged2, $logged1 );
		$this->assertIsArray( $messages );
		$this->assertCount( 2, $messages );
		$this->assertIsArray( $messages_empty );
		$this->assertCount( 1, $messages_empty );
		$this->assertSame( [ ErrorLogger::IS_EMPTY, ErrorLogger::INVALID_TYPE_NON_STRING ], $codes );
		$this->assertStringContainsString( 'Invalid type given for', $last_message );
	}

	public function test_get_errors_is_empty_array_if_no_errors() {

		$logger = new ErrorLogger();

		$this->assertSame( [ ], $logger->get_error_messages() );
	}

	public function test_get_errors_throws_if_bad_code() {
        static::expectException(\InvalidArgumentException::class);
		$logger = new ErrorLogger();
		$logger->log_error( ErrorLogger::IS_EMPTY );
		$logger->get_error_messages( [ ] );
	}

	public function test_get_errors_throws_if_bad_template() {
        static::expectException(\InvalidArgumentException::class);
		$logger = new ErrorLogger();
		$logger->log_error( ErrorLogger::IS_EMPTY, [ ], [ ] );
	}

	public function test_merge() {

		$logger = new ErrorLogger();
		$logger->log_error( ErrorLogger::IS_EMPTY );
		$logger->log_error( ErrorLogger::INVALID_DNS );

		$logger2 = new ErrorLogger();
		$logger2->log_error( ErrorLogger::IS_EMPTY );
		$logger2->log_error( ErrorLogger::NOT_IN_ARRAY );

		$merged   = $logger->merge( $logger2 );
		$codes    = $merged->get_error_codes();
		$messages = $merged->get_error_messages();

		$this->assertInstanceOf( ErrorLogger::class, $merged );
		$this->assertNotSame( $merged, $logger );
		$this->assertNotSame( $merged, $logger2 );

		$this->assertCount( 4, $merged );
		$this->assertCount( 3, $codes );
		$this->assertCount( 4, $messages );
		$this->assertTrue( in_array( ErrorLogger::IS_EMPTY, $codes, true) );
		$this->assertTrue( in_array(ErrorLogger::INVALID_DNS, $codes, true) );
		$this->assertTrue( in_array(ErrorLogger::NOT_IN_ARRAY, $codes, true) );

	}

	/**
	 * Tests is possible to use use_error_template to replace message for default error codes.
	 */
	public function test_use_error_template_replace_default_message() {

		$logger = new ErrorLogger();
		$logger->use_error_template( ErrorLogger::IS_EMPTY, '%value% is empty, bro!' );
		$logger->log_error( ErrorLogger::IS_EMPTY, [ 'value' => 'Test' ] );

		$this->assertSame( 'Test is empty, bro!', $logger->get_last_message() );
	}

	/**
	 * Tests is possible to use use_error_template to let logger make use of custom error codes.
	 */
	public function test_use_error_template_add_custom_message() {

		$logger = new ErrorLogger();

		$logger->use_error_template( 'custom_error', 'Custom error for %value%.' );

		$logger->log_error( 'custom_error', [ 'value' => 'Test' ] );

		$this->assertSame( 'Custom error for Test.', $logger->get_last_message() );
	}

	/**
	 * Tests that a custom template can be used for messages, with atomic control
	 */
	public function test_custom_template() {

		$logger = new ErrorLogger();
		$logger->log_error( ErrorLogger::IS_EMPTY );
		$logger->log_error( ErrorLogger::IS_EMPTY, [ 'value' => 'Inpsyde' ], '%value% rocks!' );
		$logger->log_error( ErrorLogger::IS_EMPTY );

		$messages = $logger->get_error_messages();
		$first    = array_shift( $messages );

		$this->assertStringContainsString( 'should not be empty', $first );
		$this->assertSame( 'Inpsyde rocks!', array_shift( $messages ) );
		$this->assertSame( $first, array_shift( $messages ) ); // 1st and 3rd are the same
	}

	public function test_count() {

		$logger = new ErrorLogger();
		$logger->log_error( ErrorLogger::IS_EMPTY );
		$logger->log_error( ErrorLogger::IS_EMPTY );
		$logger->log_error( ErrorLogger::INVALID_DNS );
		$logger->log_error( ErrorLogger::INVALID_TYPE_NON_STRING );
		$logger->log_error( ErrorLogger::IS_EMPTY );

		$this->assertCount( 5, $logger );

	}

	/**
	 * @dataProvider provide__data_for_as_string
	 *
	 * @param mixed  $value
	 * @param string $expected
	 */
	public function test_as_string( $value, $expected ) {

		$logger = new ErrorLogger();
		$logger->use_error_template( 'test', '%value%' );
		$logger->log_error( 'test', [ 'value' => $value ] );

		$this->assertSame( $expected, $logger->get_last_message( 'test' ) );
	}

	public function test_get_iterator() {

		$logger = new ErrorLogger();
		$logger->log_error( ErrorLogger::IS_EMPTY );
		$logger->log_error( ErrorLogger::IS_EMPTY );
		$logger->log_error( ErrorLogger::IS_EMPTY );

		$expected = 'This value should not be empty.';
		$it       = $logger->getIterator();

		$this->assertInstanceOf( 'Iterator', $it );

		foreach ( $it as $message ) {
			$this->assertSame( $expected, $message );
		}
	}

	/**
	 * @return array
	 */
	public function provide__data_for_as_string() {

		return [
			[ 'a string', 'a string' ],
			[ 'a string with a %percent sign', 'a string with a %percent sign' ],
			[ NULL, 'NULL' ],
			[ TRUE, '(boolean) TRUE' ],
			[ FALSE, '(boolean) FALSE' ],
			[ 10, '(integer) 10' ],
			[ 10.0, '(double) 10' ],
			[ [ 'an', 'array' ], var_export( [ 'an', 'array' ], TRUE ) ],
			[ (object) [ 'an' => 'object' ], '(object) stdClass' ]
		];
	}

	/**
	 * Used to access internal property of Logger
	 *
	 * @param ErrorLogger $logger
	 *
	 * @return array
	 */
	private function get_messages( ErrorLogger $logger ) {

		$getter = \Closure::bind(
			function () {

				return $this->messages;
			}, $logger, ErrorLogger::class
		);

		return $getter();
	}
}