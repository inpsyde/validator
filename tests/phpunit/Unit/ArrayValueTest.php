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

use Inpsyde\Validator\ArrayValue;

/**
 * Class ArrayValueTest
 *
 * @author  Christian Brückner <chris@chrico.info>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
class ArrayValueTest extends AbstractTestCase {

	/**
	 * Basic test with not validator should return TRUE.
	 */
	public function test_basic() {

		$testee = new ArrayValue();
		$this->assertTrue( $testee->is_valid( [ 'key' => 'value', ] ) );
	}

	/**
	 * Tests the different input types and expected result.
	 *
	 * @dataProvider provide__value_types
	 */
	public function test__invalid_value_type( $input, $expected ) {

		$testee = new ArrayValue();
		$this->assertEquals( $expected, $testee->is_valid( $input ) );
	}

	/**
	 * Returns a dataSet of different input values with excepted result.
	 *
	 * @return array
	 */
	public function provide__value_types() {

		return [
			'valid_array'         => [ [ 'key' => 'value' ], TRUE ],
			'string'              => [ '', FALSE ],
			'int'                 => [ 1, FALSE ],
			'boolean'             => [ TRUE, FALSE ]
		];

	}

	/**
	 * Basic test with one validator to validate all array-values.
	 */
	public function test_basic_add_validator() {

		$testee = new ArrayValue();
		$testee->add_validator( $this->getMockValidator() );

		$this->assertTrue( $testee->is_valid( [ 'key' => 'value', ] ) );
	}

	/**
	 * Test multiple validators for all array-values, where 1 validator return FALSE.
	 */
	public function test_multiple_add_validator() {

		$testee = new ArrayValue();

		// some "TRUE"-validator.
		$testee->add_validator( $this->getMockValidator() );

		// this should stop the validation-process.
		$testee->add_validator( $this->getMockValidator( FALSE ) );

		// this validator should not be called, because the validator before returns FALSE;
		$testee->add_validator( $this->getMockValidator( TRUE, 0 ) );

		$this->assertFalse( $testee->is_valid( [ 'key' => 'value', ] ) );
	}

	/**
	 * Basic test with one validator added by array-key.
	 */
	public function test_basic_add_validator_by_key() {

		$testee = new ArrayValue();
		$testee->add_validator_by_key( $this->getMockValidator(), 'key' );

		// this validator should not be called.
		$testee->add_validator_by_key( $this->getMockValidator( TRUE, 0 ), 'some non existing key.' );

		$this->assertTrue( $testee->is_valid( [ 'key' => 'value', ] ) );
	}

	/**
	 * Test if invalid key-types will throw an Exception.
	 *
	 * @dataProvider provide__invalid_keys
	 */
	public function test_invalid_key_type( $key ) {
        static::expectException(\InvalidArgumentException::class);
		$testee = new ArrayValue();
		$testee->add_validator_by_key( $this->getMockValidator( FALSE, 0 ), $key );
	}

	public function provide__invalid_keys() {

		return [
			'array'     => [ [ ] ],
			'stdClass'  => [ new \stdClass() ],
			'ressource' => [ fopen( 'php://stdin', 'r+' ) ]
		];
	}

	public function test_multiple_add_validator_by_key() {

		$testee = new ArrayValue();

		// some "TRUE"-validator for array-key "key_1".
		$testee->add_validator_by_key( $this->getMockValidator(), 'key_1' );

		// this validator should not be called, because the key does not exists in test-array.
		$testee->add_validator_by_key( $this->getMockValidator( TRUE, 0 ), 'some undefined key' );

		// adding a validator which returns "FALSE" should stop the validation-process.
		$testee->add_validator_by_key( $this->getMockValidator( FALSE ), 'key_2' );

		// this validator should not be called, because the validator before returns FALSE;
		$testee->add_validator_by_key( $this->getMockValidator( TRUE, 1 ), 'key_1' );

		$this->assertFalse( $testee->is_valid( [ 'key_1' => 'value_2', 'key_2' => 'value_2' ] ) );
	}

	/**
	 * @param bool $return_value
	 * @param int  $called
	 */
	private function getMockValidator( $return_value = TRUE, $called = 1 ) {

        $mock = $this->createMock('\Inpsyde\Validator\ValidatorInterface');
        $mock
            ->expects( $this->exactly($called) )
            ->method('is_valid')
            ->willReturn($this->returnValue($return_value));

		return $mock;
	}

}