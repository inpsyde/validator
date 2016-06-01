<?php

namespace Inpsyde\Validator\Tests\Unit;

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

}