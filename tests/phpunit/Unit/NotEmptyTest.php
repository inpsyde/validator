<?php

namespace Inpsyde\Validator\Tests\Unit;

use Inpsyde\Validator\NotEmpty;

/**
 * Class TestNotEmpty
 *
 * @package Inpsyde\Tests\Validator
 */
class tNotEmptyTes extends \PHPUnit_Framework_TestCase {

	/**
	 * Ensures that the validator follows expected behavior
	 *
	 * @param mixed   $value Value to test
	 * @param boolean $valid Expected validity of value
	 *
	 * @dataProvider provide__basic_data
	 */
	public function test_basic( $value, $valid ) {

		$validator = new NotEmpty();
		$is_valid  = $validator->is_valid( $value );
		if ( $valid ) {
			$this->assertTrue( $is_valid );
		} else {
			$this->assertFalse( $is_valid );
		}
	}

	/**
	 * @return array
	 */
	public function provide__basic_data() {

		return [
			// input value, excepted is value valid/not valid
			'valid_default'              => [ 'word', TRUE ],
			'invalid_default'            => [ '', FALSE ],
			'valid_whitespaces'          => [ '    ', TRUE ],
			'valid_whitespaces_and_word' => [ '  word  ', TRUE ],
			'valid_0_as_string'          => [ '0', TRUE ],
			'valid_int_1'                => [ 1, TRUE ],
			'valid_int_0'                => [ 0, TRUE ],
			'valid_TRUE'                 => [ TRUE, TRUE ],
			'invalid_FALSE'              => [ FALSE, FALSE ],
			'invalid_NULL'               => [ NULL, FALSE ],
			'invalid_empty_array'        => [ [ ], FALSE ],
			'valid_array_with_int'       => [ [ 5 ], TRUE ],
			'valid_float'                => [ 1.0, TRUE ],
			'valid_stdClass'             => [ new \stdClass(), TRUE ]
		];
	}

}