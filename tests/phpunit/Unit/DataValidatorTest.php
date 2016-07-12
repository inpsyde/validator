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

use Inpsyde\Validator\DataValidator;
use Inpsyde\Validator\Error\ErrorLoggerInterface;
use Inpsyde\Validator\ExtendedValidatorInterface;
use Mockery;

/**
 * Class DataValidatorTest
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
class DataValidatorTest extends \PHPUnit_Framework_TestCase {

	public function test_constructor_build_logger() {

		$validator = new DataValidator();

		$logger = $this->get_validator_logger( $validator );

		$this->assertInstanceOf( ErrorLoggerInterface::class, $logger );

	}

	public function test_constructor_use_logger_if_provided() {

		$logger    = \Mockery::mock( ErrorLoggerInterface::class );
		$validator = new DataValidator( $logger );

		$this->assertSame( $logger, $this->get_validator_logger( $validator ) );
	}

	public function test_with_error_logger() {

		$validator = new DataValidator();
		$logger    = \Mockery::mock( ErrorLoggerInterface::class );
		$logger
			->shouldReceive( 'get_error_codes' )
			->andReturn( [ ] );

		$validator_logger = $validator->with_error_logger( $logger );

		$this->assertInstanceOf( get_class( $validator ), $validator_logger );
		$this->assertInstanceOf( ErrorLoggerInterface::class, $this->get_validator_logger( $validator ) );
		$this->assertInstanceOf( ErrorLoggerInterface::class, $this->get_validator_logger( $validator_logger ) );
		$this->assertNotSame(
			$this->get_validator_logger( $validator ),
			$this->get_validator_logger( $validator_logger )
		);
		$this->assertNotSame( $logger, $this->get_validator_logger( $validator_logger ) );

	}

	public function test_add_validators() {

		$ok_validator = Mockery::mock( ExtendedValidatorInterface::class );
		$ok_validator
			->shouldReceive( 'is_valid' )
			->twice()
			->andReturn( TRUE );

		$fail_validator = Mockery::mock( ExtendedValidatorInterface::class );
		$fail_validator
			->shouldReceive( 'is_valid' )
			->once()
			->with( 'test' )
			->andReturn( FALSE );

		$fail_validator
			->shouldReceive( 'is_valid' )
			->once()
			->with( 'test2' )
			->andReturn( FALSE );

		$fail_validator
			->shouldReceive( 'get_error_code' )
			->twice()
			->andReturn( 'custom' );

		$fail_validator
			->shouldReceive( 'get_input_data' )
			->twice()
			->andReturn( [ 'value' => 'test' ], [ 'value' => 'test2' ] );

		$test2_validator = Mockery::mock( ExtendedValidatorInterface::class );
		$test2_validator
			->shouldReceive( 'is_valid' )
			->once()
			->with( 'test2' )
			->andReturn( FALSE );

		$test2_validator
			->shouldReceive( 'get_error_code' )
			->once()
			->andReturn( 'custom2' );

		$test2_validator
			->shouldReceive( 'get_input_data' )
			->once()
			->andReturn( [ 'value' => 'test2' ] );

		$logger = Mockery::mock( ErrorLoggerInterface::class );

		$logger
			->shouldReceive( 'log_error_for_key' )
			->with( 'test_key', 'custom', [ 'value' => 'test', 'key' => 'test_key' ], NULL );

		$logger
			->shouldReceive( 'log_error_for_key' )
			->with( 'test_key2', 'custom', [ 'value' => 'test2', 'key' => 'test_key2' ], NULL );

		$logger
			->shouldReceive( 'log_error_for_key' )
			->with( 'test_key2', 'custom2', [ 'value' => 'test2', 'key' => 'test_key2' ], NULL );

		$validator = new DataValidator( $logger );

		$expected_data = [
			'custom'  => [
				[
					'value' => 'test',
					'key'   => 'test_key'
				],
				[
					'value' => 'test2',
					'key'   => 'test_key2'
				],
			],
			'custom2' => [
				[
					'value' => 'test2',
					'key'   => 'test_key2'
				],
			]
		];

		$validator
			->add_validator( $ok_validator )
			->add_validator( $fail_validator )
			->add_validator_by_key( $test2_validator, 'test_key2' );

		$this->assertFalse( $validator->is_valid( [ 'test_key' => 'test', 'test_key2' => 'test2' ] ) );
		$this->assertSame( $expected_data, $validator->get_error_data() );
		$this->assertSame( $expected_data[ 'custom' ], $validator->get_error_data( 'custom' ) );
		$this->assertSame( $expected_data[ 'custom2' ], $validator->get_error_data( 'custom2' ) );
	}

	public function test_add_validator_with_messages() {

		$leaf_validator_1 = Mockery::mock( ExtendedValidatorInterface::class );
		$leaf_validator_1
			->shouldReceive( 'is_valid' )
			->andReturn( FALSE );

		$leaf_validator_1
			->shouldReceive( 'get_error_code' )
			->andReturn( ErrorLoggerInterface::CUSTOM_ERROR );

		$leaf_validator_2 = clone $leaf_validator_1;

		$leaf_validator_1
			->shouldReceive( 'get_input_data' )
			->times( 4 )
			->andReturn( [ 'value' => 'test' ], [ 'value' => 'test2' ], [ 'value' => 'test' ], [ 'value' => 'test2' ] );

		$leaf_validator_2
			->shouldReceive( 'get_input_data' )
			->times( 2 )
			->andReturn( [ 'value' => 'test' ], [ 'value' => 'test2' ] );

		$validator = new DataValidator();
		$validator
			->add_validator_by_key( $leaf_validator_1, 'a', 'Leaf ONE: "%value%" is not valid for key "%key%".' )
			->add_validator_with_message( $leaf_validator_1, 'Leaf ONE: "%value%" is not valid for key "%key%".' )
			->add_validator_by_key( $leaf_validator_1, 'b' )
			->add_validator_by_key( $leaf_validator_2, 'a', 'Leaf TWO: "%value%" is not valid for key "%key%".' )
			->add_validator_by_key(
				$leaf_validator_2,
				[ 'b' => 'Bee' ],
				'Leaf TWO: "%value%" is not valid for key "%key%".'
			);

		$valid = $validator->is_valid( [ 'a' => 'test', 'b' => 'test2' ] );

		$expected = [
			'Leaf ONE: "test" is not valid for key "a".',
			'Leaf ONE: "test2" is not valid for key "b".',
			'Leaf ONE: "test" is not valid for key "a".',
			'Leaf TWO: "test" is not valid for key "a".',
			'<code>b</code>: Some errors occurred for <code>test2</code>.',
			'Leaf TWO: "test2" is not valid for key "Bee".',
		];

		$this->assertFalse( $valid );
		$this->assertSame( $expected, $validator->get_error_messages() );
	}

	public function test_validator_map() {

		$false_1 = Mockery::mock( ExtendedValidatorInterface::class );
		$false_1
			->shouldReceive( 'is_valid' )
			->andReturn( FALSE );

		$false_1
			->shouldReceive( 'get_error_code' )
			->andReturn( ErrorLoggerInterface::CUSTOM_ERROR );

		$false_1
			->shouldReceive( 'get_input_data' )
			->andReturn( [ 'value' => 'foo' ], [ 'value' => 'bar' ] );

		$true_1 = Mockery::mock( ExtendedValidatorInterface::class );
		$true_1
			->shouldReceive( 'is_valid' )
			->andReturn( TRUE );

		$validator = new DataValidator();

		$validator->add_validator_map(
			[
				'a' => $false_1,
				'b' => clone $false_1,
				'c' => $true_1,
				'd' => clone $true_1,
			]
		);

		$this->assertCount( 4, $validator );
		$this->assertFalse( $validator->is_valid( [ 'a' => 'foo', 'b' => 'bar', 'c' => 'foo', 'd' => 'bar' ] ) );
		$this->assertSame( [ ErrorLoggerInterface::CUSTOM_ERROR ], $validator->get_error_codes() );
		$this->assertCount( 2, $validator->get_error_messages() );

	}

	/**
	 * @param DataValidator $validator
	 *
	 * @return ErrorLoggerInterface
	 */
	private function get_validator_logger( DataValidator $validator ) {

		$getter = \Closure::bind(
			function () {

				return $this->error_logger;
			},
			$validator,
			DataValidator::class
		);

		return $getter();
	}
}