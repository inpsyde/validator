<?php

namespace Inpsyde\Tests\Unit\Validator;

use Inpsyde\Validator\RegEx;

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
				$validator->is_valid( $input ),
				implode( "\n", $validator->get_error_messages() )
			);
		}
	}

	/**
	 * @return array
	 */
	public function provide__basic() {

		return [
			"chars_valid"   => [
				'/[a-z]/',
				TRUE,
				[ 'abc123', 'foo', 'a', 'z' ]
			],
			"chars_invalid" => [
				'/[a-z]/',
				FALSE,
				[ '123', 'A' ]
			]
		];
	}

}