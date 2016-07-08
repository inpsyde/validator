<?php

namespace Inpsyde\Validator\Tests\Unit;

use Inpsyde\Validator\Error\ErrorLoggerInterface;
use Inpsyde\Validator\RegEx;

/**
 * Class RegExTest
 *
 * @package Inpsyde\Validator\Tests\Unit
 */
class RegExTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Ensures that the validator follows expected behavior
	 *
	 * @dataProvider provide__basic
	 *
	 * @param string $pattern
	 * @param bool   $expected
	 * @param array  $input_values
	 *
	 * @return void
	 */
	public function test_basic( $pattern, $expected, $input_values ) {

		$validator = new RegEx( [ 'pattern' => $pattern ] );
		foreach ( $input_values as $input ) {
			$this->assertEquals(
				$expected,
				$validator->is_valid( $input )
			);
		}
	}

	/**
	 * Tests that error code is returned according to validation results and options.
	 */
	public function test_get_error_code() {

		$validator = new RegEx( [ 'pattern' => 'foo' ] );

		$validator->is_valid( new \stdClass() );
		$code_non_scalar = $validator->get_error_code();

		$validator->is_valid( 'XXX' );
		$code_non_match = $validator->get_error_code();

		$validator_err = new RegEx( [ 'pattern' => '(foo' ] );
		$validator_err->is_valid( 'foo' );
		$code_err = $validator_err->get_error_code();

		$this->assertSame( ErrorLoggerInterface::INVALID_TYPE_NON_SCALAR, $code_non_scalar );
		$this->assertSame( ErrorLoggerInterface::NOT_MATCH, $code_non_match );
		$this->assertSame( ErrorLoggerInterface::REGEX_INTERNAL_ERROR, $code_err );
	}

	/**
	 * Tests that input data is returned according to validation results and options.
	 */
	public function test_get_input_data() {

		$validator = new RegEx( [ 'pattern' => 'foo' ] );

		$validator->is_valid( 'foo' );
		$input = $validator->get_input_data();

		$this->assertInternalType( 'array', $input );
		$this->assertArrayHasKey( 'value', $input );
		$this->assertSame( 'foo', $input[ 'value' ] );

		$validator->is_valid( 'bar' );

		$input = $validator->get_input_data();
		$this->assertSame( 'bar', $input[ 'value' ] );
	}

	/**
	 * @return array
	 */
	public function provide__basic() {

		return [
			"chars_valid"              => [
				'/[a-z]/',
				TRUE,
				[ 'abc123', 'foo', 'a', 'z' ]
			],
			"chars_invalid"            => [
				'/[a-z]/',
				FALSE,
				[ '123', 'A' ]
			],
			"chars_valid_add_boundary" => [
				'[a-z]',
				TRUE,
				[ 'abc123', 'foo', 'a', 'z' ]
			],
			"chars_valid_bad_pattern"  => [
				'/[a-z]',
				FALSE,
				[ 'abc123', 'foo', 'a', 'z' ]
			],
		];
	}

}