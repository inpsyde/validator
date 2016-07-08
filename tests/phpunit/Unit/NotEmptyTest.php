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
use Inpsyde\Validator\NotEmpty;

/**
 * Class TestNotEmpty
 *
 * @author  Christian Brückner <chris@chrico.info>
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
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
	 * Tests that error code is returned according to validation results and options.
	 */
	public function test_get_error_code() {

		$validator = new NotEmpty();
		$validator->is_valid( '' );
		$code = $validator->get_error_code();

		$this->assertSame( ErrorLoggerInterface::IS_EMPTY, $code );
	}

	/**
	 * Tests that input data is returned according to validation results and options.
	 */
	public function test_get_input_data() {

		$validator = new NotEmpty();

		$validator->is_valid( 'x' );
		$input = $validator->get_input_data();

		$this->assertInternalType( 'array', $input );
		$this->assertArrayHasKey( 'value', $input );
		$this->assertSame( 'x', $input[ 'value' ] );

		$validator->is_valid( 'y' );

		$input = $validator->get_input_data();
		$this->assertSame( 'y', $input[ 'value' ] );
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