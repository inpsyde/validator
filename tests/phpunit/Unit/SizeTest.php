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
use Inpsyde\Validator\Size;
use Mockery;

/**
 * Class SizeTest
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
class SizeTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Ensures that the validator follows expected behavior
	 *
	 * @dataProvider provide_is_valid_cases
	 *
	 * @param string $input
	 * @param int    $size
	 * @param bool   $expect
	 */
	public function test_is_valid( $input, $size, $expect ) {

		$validator = new Size( [ 'size' => $size ] );

		$input_str = is_scalar( $input ) ? (string) $input : serialize( $input );

		$expect
			? $this->assertTrue( $validator->is_valid( $input ), "{$input_str} size is not {$size}." )
			: $this->assertFalse( $validator->is_valid( $input ), "{$input_str} size is {$size}." );
	}

	/**
	 * @return array
	 */
	public function provide_is_valid_cases() {

		return [
			// $input, $size, $expect
			[ 'foo bar', 6, FALSE ],
			[ 'foo bar', 7, TRUE ],
			[ '12345', 5, TRUE ],
			[ '', 0, TRUE ],
			[ ' ', 0, FALSE ],
			[ ' ', 1, TRUE ],
			[ 1, 1, TRUE ],
			[ 12345, 12345, TRUE ],
			[ 1.9, 1, TRUE ],
			[ 9.9, 10, FALSE ],
			[ 0755, (int) 0755, TRUE ],
			[ [ NULL ], 1, TRUE ],
			[ [ ], 0, TRUE ],
			[ [ '', '2' ], 2, TRUE ],
			[ new \ArrayObject( [ '', '2' ] ), 2, TRUE ],
			[ new \ArrayObject(), 0, TRUE ],
			[ new \ArrayObject(), 1, FALSE ],
			[ (object) [ 'foo' => 'bar' ], 1, TRUE ],
		];
	}

	/**
	 * Tests that error code is returned according to validation results and options.
	 */
	public function test_get_error_code() {

		$validator = new Size( [ 'size' => 1 ] );

		$validator->is_valid( Mockery::mock() );
		$code_non_countable = $validator->get_error_code();

		$validator->is_valid( STDIN );
		$code_non_countable_res = $validator->get_error_code();

		$validator->is_valid( 2 );
		$code_non_size = $validator->get_error_code();

		$this->assertSame( ErrorLoggerInterface::INVALID_TYPE_NON_COUNTABLE, $code_non_countable );
		$this->assertSame( ErrorLoggerInterface::INVALID_TYPE_NON_COUNTABLE, $code_non_countable_res );
		$this->assertSame( ErrorLoggerInterface::INVALID_SIZE, $code_non_size );
	}

}