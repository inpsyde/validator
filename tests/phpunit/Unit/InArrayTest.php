<?php

namespace Inpsyde\Validator\Tests\Unit;

use Inpsyde\Validator\InArray;

/**
 * Class InArrayTest
 *
 * @package Inpsyde\Validator\Tests\Unit
 */
class InArrayTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Ensures that the validator follows expected behavior
	 *
	 * @dataProvider provide__basic_data
	 *
	 * @param array $inputs
	 * @param bool  $expected
	 *
	 * @return void
	 */
	public function test_basic( $inputs, $expected ) {

		$options   = [
			'haystack' => [ 1, 'a', FALSE, NULL ]
		];
		$validator = new InArray( $options );

		foreach ( $inputs as $input ) {
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
	public function provide__basic_data() {

		return [
			// inputs, expected
			'valid'   => [
				[ 1, 'a', FALSE, NULL ],
				TRUE
			],
			'invalid' => [
				[ 0, 'A', TRUE ],
				FALSE
			]
		];
	}
}