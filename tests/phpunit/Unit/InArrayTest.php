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
use Inpsyde\Validator\InArray;

/**
 * Class InArrayTest
 *
 * @author  Christian Brückner <chris@chrico.info>
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
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
				$validator->is_valid( $input )
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

	/**
	 * Tests that error code is returned according to validation results and options.
	 */
	public function test_get_error_code() {

		$validator = new InArray( [ 'haystack' => [ 'foo', '1' ] ] );
		$validator->is_valid( 1 );
		$code = $validator->get_error_code();

		$this->assertSame( ErrorLoggerInterface::NOT_IN_ARRAY, $code );
	}

	/**
	 * Tests that input data is returned according to validation results and options.
	 */
	public function test_get_input_data() {

		$validator = new InArray();

		$validator->is_valid( 'foo' );
		$input = $validator->get_input_data();

		$this->assertInternalType( 'array', $input );
		$this->assertArrayHasKey( 'value', $input );
		$this->assertSame( 'foo', $input[ 'value' ] );

		$validator->is_valid( 'bar' );

		$input = $validator->get_input_data();
		$this->assertSame( 'bar', $input[ 'value' ] );
	}

}