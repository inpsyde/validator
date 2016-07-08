<?php

namespace Inpsyde\Validator\Tests\Unit;

use Inpsyde\Validator\Error\ErrorLoggerInterface;
use Inpsyde\Validator\LessThan;

/**
 * Class LessThanTest
 *
 * @package Inpsyde\Validator\Tests\Unit
 */
class LessThanTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Ensures that the validator follows expected behavior
	 *
	 * @dataProvider provide__basic_data
	 */
	public function test_basic( $options, $excepted, $values ) {

		$validator = new LessThan( $options );
		foreach ( $values as $value ) {
			$this->assertEquals(
				$excepted,
				$validator->is_valid( $value )
			);
		}

	}

	/**
	 * @return array
	 */
	public function provide__basic_data() {

		return [
			// options, excepted, values
			'valid_default'         => [
				[ 'max' => 100 ],
				TRUE,
				[ - 1, 0, 0.01, 1, 99.999 ]
			],
			'invalid_deafult'       => [
				[ 'max' => 100 ],
				FALSE,
				[ 100, 100.0, 100.01 ]
			],
			'valid_with_char'       => [
				[ 'max' => 'z' ],
				TRUE,
				[ 'x', 'y' ]
			],
			'invalid_with_char'     => [
				[ 'max' => 'a' ],
				FALSE,
				[ 'b', 'c', 'd' ]
			],
			'valid_inclusive'       => [
				[ 'max' => 100, 'inclusive' => TRUE ],
				TRUE,
				[ - 1, 0, 0.01, 1, 99.999, 100, 100.0 ]
			],
			'invalid_inclusive'     => [
				[ 'max' => 100, 'inclusive' => TRUE ],
				FALSE,
				[ 100.01 ]
			],
			'valid_not_inclusive'   => [
				[ 'max' => 100, 'inclusive' => FALSE ],
				TRUE,
				[ - 1, 0, 0.01, 1, 99.999 ]
			],
			'invalid_not_inclusive' => [
				[ 'max' => 100, 'inclusive' => FALSE ],
				FALSE,
				[ 100, 100.0, 100.01 ]
			]
		];
	}

	/**
	 * Tests that error code is returned according to validation results and options.
	 */
	public function test_get_error_code() {

		$validator = new LessThan( [ 'max' => 2, 'inclusive' => FALSE ] );
		$validator->is_valid( 2 );
		$code = $validator->get_error_code();

		$validator_inc = new LessThan( [ 'max' => 2, 'inclusive' => TRUE ] );
		$validator_inc->is_valid( 3 );
		$code_inc = $validator_inc->get_error_code();

		$this->assertSame( ErrorLoggerInterface::NOT_LESS, $code );
		$this->assertSame( ErrorLoggerInterface::NOT_LESS_INCLUSIVE, $code_inc );
	}

	/**
	 * Tests that input data is returned according to validation results and options.
	 */
	public function test_get_input_data() {

		$validator = new LessThan();

		$validator->is_valid( 1 );
		$input = $validator->get_input_data();

		$this->assertInternalType( 'array', $input );
		$this->assertArrayHasKey( 'value', $input );
		$this->assertSame( 1, $input[ 'value' ] );

		$validator->is_valid( 2 );

		$input = $validator->get_input_data();
		$this->assertSame( 2, $input[ 'value' ] );
	}

}