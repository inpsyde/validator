<?php

namespace Inpsyde\Validator\Tests\Unit;

use Inpsyde\Validator\ArrayValue;

/**
 * Class ArrayValueTest
 *
 * @package Inpsyde\Validator\Tests\Unit
 */
class ArrayValueTest extends \PHPUnit_Framework_TestCase {

	public function test_basic() {

		$mock = $this->getMock( '\Inpsyde\Validator\ValidatorInterface' );
		$mock->method( 'is_valid' )
		     ->will( $this->returnValue( TRUE ) );

		$validator = new ArrayValue();
		$validator->add_validator( 'key', $mock );

		$value = [
			'key' => 'value',
		];

		$this->assertTrue( $validator->is_valid( $value ) );
	}

	public function test_multiple() {

		$mock_1 = $this->getMock( '\Inpsyde\Validator\ValidatorInterface' );
		$mock_1->method( 'is_valid' )
		       ->will( $this->returnValue( TRUE ) );

		$mock_2 = $this->getMock( '\Inpsyde\Validator\ValidatorInterface' );
		$mock_2->method( 'is_valid' )
		       ->will( $this->returnValue( FALSE ) );

		$validator = new ArrayValue();
		$validator->add_validator( 'key_1', $mock_1 );
		$validator->add_validator( 'key_2', $mock_2 );

		$value = [
			'key_1' => 'value_1',
			'key_2' => 'value_2'
		];

		$this->assertFalse( $validator->is_valid( $value ) );
	}

}