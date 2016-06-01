<?php

namespace Inpsyde\Tests\Unit\Validator;

use Inpsyde\Validator\GreaterThan;

class GreaterThanTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Ensures that the validator follows expected behavior
	 *
	 * @dataProvider provide__basic_data
	 */
	public function test_basic( $options, $excepted, $values ) {

		$validator = new GreaterThan( $options );
		foreach ( $values as $value ) {
			$this->assertEquals(
				$excepted,
				$validator->is_valid( $value ),
				implode( "\n", $validator->get_error_messages() )
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
				[ 'min' => 0 ],
				TRUE,
				[ 0.01, 1, 100 ]
			],
			'invalid_default'       => [
				[ 'min' => 0 ],
				FALSE,
				[ 0, 0.00, - 0.01, - 1, - 100 ]
			],
			'valid_char'            => [
				[ 'min' => 'a' ],
				TRUE,
				[ 'b', 'c', 'd' ]
			],
			'invalid_char'          => [
				[ 'min' => 'z' ],
				FALSE,
				[ 'x', 'y', 'z' ]
			],
			'valid_inclusive'       => [
				[ 'min' => 0, 'inclusive' => TRUE ],
				TRUE,
				[ 0, 0.00, 0.01, 1, 100 ]
			],
			'invalid_inclusive'     => [
				[ 'min' => 0, 'inclusive' => TRUE ],
				FALSE,
				[ - 0.01, - 1, - 100 ]
			],
			'valid_not_inclusive'   => [
				[ 'min' => 0, 'inclusive' => FALSE ],
				TRUE,
				[ 0.01, 1, 100 ]
			],
			'invalid_not_inclusive' => [
				[ 'min' => 0, 'inclusive' => FALSE ],
				FALSE,
				[ 0, 0.00, - 0.01, - 1, - 100 ]
			]
		];
	}

}