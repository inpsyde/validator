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
use Inpsyde\Validator\Type;
use Mockery;

/**
 * Class TypeTest
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
class TypeTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function test_type_is_required() {

		new Type();

	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function test_type_need_to_be_string() {

		new Type( [ 'type' => TRUE ] );

	}

	/**
	 * Ensures that the validator follows expected behavior
	 *
	 * @dataProvider provide_is_valid_cases
	 *
	 * @param string $input
	 * @param string $type
	 * @param bool   $expect
	 */
	public function test_is_valid( $input, $type, $expect ) {

		$validator = new Type( [ 'type' => $type ] );

		$input_str = is_scalar( $input ) ? (string) $input : serialize( $input );

		$expect
			? $this->assertTrue( $validator->is_valid( $input ), "{$input_str} type is not {$type}." )
			: $this->assertFalse( $validator->is_valid( $input ), "{$input_str} type is {$type}." );
	}

	/**
	 * @return array
	 */
	public function provide_is_valid_cases() {

		return [
			// $input, $type, $expect
			[ 'foo bar', 'string', TRUE ],
			[ 1, 'string', FALSE ],
			[ '', 'string', TRUE ],
			[ 'CAPS', 'string', TRUE ],
			[ 2, 'integer', TRUE ],
			[ 3, 'int', TRUE ],
			[ 0, 'int', TRUE ],
			[ 0.0, 'int', FALSE ],
			[ 3.0, 'int', FALSE ],
			[ 4.0, 'integer', FALSE ],
			[ 4.5, 'float', TRUE ],
			[ 5.5, 'double', TRUE ],
			[ 6.5, 'DOUBLE', TRUE ],
			[ 6.5, 'float', TRUE ],
			[ TRUE, 'bool', TRUE ],
			[ TRUE, 'boolean', TRUE ],
			[ FALSE, 'bool', TRUE ],
			[ FALSE, 'boolean', TRUE ],
			[ 1, 'bool', FALSE ],
			[ 0, 'boolean', FALSE ],
			[ NULL, 'NULL', TRUE ],
			[ NULL, 'null', TRUE ],
			[ '', 'null', FALSE ],
			[ 3, 'numeric', TRUE ],
			[ 0, 'numeric', TRUE ],
			[ 0.0, 'numeric', TRUE ],
			[ 0.0, 'Numeric', TRUE ],
			[ '2.0', 'numeric', TRUE ],
			[ '200', 'numeric', TRUE ],
			[ 'one', 'numeric', FALSE ],
			[ 3, 'number', TRUE ],
			[ 0, 'number', TRUE ],
			[ 0.0, 'number', TRUE ],
			[ 0.0, 'number', TRUE ],
			[ '2.0', 'number', TRUE ],
			[ '200', 'number', TRUE ],
			[ 'one', 'number', FALSE ],
			[ new \ArrayObject(), 'traversable', TRUE ],
			[ [ ], 'traversable', TRUE ],
			[ [ NULL ], 'traversable', TRUE ],
			[ (object) [ ], 'object', TRUE ],
			[ [ ], 'object', FALSE ],
			[ new \ArrayObject(), 'object', TRUE ],
			[ new \ArrayObject(), \ArrayObject::class, TRUE ],
			[ new \ArrayObject(), \ArrayAccess::class, TRUE ],
			[ (object) [ ], \stdClass::class, TRUE ],
			[ [ ], \ArrayAccess::class, FALSE ],
		];
	}

}