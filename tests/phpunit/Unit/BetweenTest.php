<?php

namespace Inpsyde\Tests\Unit\Validator;

use Inpsyde\Validator\Between;

class BetweenTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @return array
	 */
	public function provide__basic_data() {

		return [
			"integer_inclusive"       => [
				[ "min" => 1, "max" => 100, "inclusive" => TRUE ],
				TRUE,
				[ 1, 10, 100 ]
			],
			"mixed_numbers_inclusive" => [
				[ "min" => 1, "max" => 100, "inclusive" => TRUE ],
				FALSE,
				[ 0, 0.99, 100.01, 101 ]
			],
			"integer_not_inclusive"   => [
				[ "min" => 1, "max" => 100, "inclusive" => FALSE ],
				FALSE,
				[ 0, 1, 100, 101 ]
			],
			"chars_inclusive"         => [
				[ "min" => 'a', "max" => 'z', "inclusive" => TRUE ],
				TRUE,
				[ 'a', 'b', 'y', 'z' ]
			],
			"chars_not_inclusive"     => [
				[ "min" => 'a', "max" => 'z', "inclusive" => FALSE ],
				FALSE,
				[ '!', 'a', 'z' ]
			]
		];
	}

	/**
	 * Ensures that the validator follows expected behavior
	 *
	 * @dataProvider provide__basic_data
	 *
	 * @param array $options
	 * @param bool  $expected
	 * @param array $input_values
	 *
	 * @return void
	 */
	public function test_basic( $options, $expected, $input_values ) {

		$validator = new Between( $options );
		foreach ( $input_values as $input ) {
			$this->assertEquals(
				$expected,
				$validator->is_valid( $input ),
				implode( "\n", $validator->get_error_messages() )
			);
		}
	}

}