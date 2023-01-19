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

use Inpsyde\Validator\Bulk;
use Inpsyde\Validator\Error\ErrorLoggerInterface;
use Inpsyde\Validator\ExtendedValidatorInterface;
use Inpsyde\Validator\GreaterThan;
use Inpsyde\Validator\NotEmpty;
use Inpsyde\Validator\Url;
use Mockery;

/**
 * Class BulkTest
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
class BulkTest extends AbstractTestCase {

	public function test_constructor_needs_validator() {
        static::expectException(\InvalidArgumentException::class);
		new Bulk();
	}

	public function test_with_validator() {

		$validator = Mockery::mock( ExtendedValidatorInterface::class )
			->shouldReceive( 'is_valid' )
			->with( 'foo' )
			->once()
			->andReturn( TRUE )
			->getMock()
			->shouldReceive( 'get_input_data' )
			->andReturn( [ 'value' => 'foo' ] )
			->getMock()
			->shouldReceive( 'get_error_code' )
			->andReturn( '' );

		$bulk = Bulk::with_validator( $validator->getMock() );

		$this->assertTrue( $bulk->is_valid( [ 'foo' ] ) );
	}

	public function test_constructor_can_use_factory() {

		$validator       = Mockery::mock( ExtendedValidatorInterface::class );
		$validator_class = get_class( $validator );

		$bulk             = new Bulk( [ 'validator' => $validator_class ] );
		$input            = $bulk->get_input_data();
		$validator_stored = $input[ 'validator' ];

		$this->assertIsObject( $validator_stored );
		$this->assertInstanceOf( $validator_class, $validator_stored );
	}

	public function test_is_valid_accepts_traversable_only() {

		$bulk  = new Bulk( [ 'validator' => Mockery::mock( ExtendedValidatorInterface::class ) ] );
		$valid = $bulk->is_valid( '' );

		$this->assertFalse( $valid );
		$this->assertSame( ErrorLoggerInterface::INVALID_TYPE_NON_TRAVERSABLE, $bulk->get_error_code() );
	}

	/**
	 * @param mixed                      $value
	 * @param ExtendedValidatorInterface $validator
	 * @param bool                       $expected_valid
	 * @param string                     $error
	 *
	 * @dataProvider is_valid_cases_provider
	 */
	public function test_is_valid( $value, $validator, $expected_valid, $error ) {

		$bulk   = new Bulk( [ 'validator' => $validator ] );
		$result = $bulk->is_valid( $value );
		$expected_valid
			? $this->assertTrue( $result, serialize( $value ) )
			: $this->assertFalse( $result, serialize( $value ) );

		$this->assertSame( $error, $bulk->get_error_code() );
	}

	public function is_valid_cases_provider() {

		return [
			[ [ 2, 4, 6 ], new GreaterThan( [ 'min' => 1 ] ), TRUE, '' ],
			[ [ 2, 4, 6 ], new GreaterThan( [ 'min' => 3 ] ), FALSE, ErrorLoggerInterface::NOT_GREATER ],
			[ [ 1, 2, 'x' ], new NotEmpty(), TRUE, '' ],
			[ [ '', NULL, FALSE ], new NotEmpty(), FALSE, ErrorLoggerInterface::IS_EMPTY ],
			[ [ 'http://example.com', 'example.com' ], new Url(), FALSE, ErrorLoggerInterface::NOT_URL ],
			[ [ 'http://example.com', 'https://example.it' ], new Url(), TRUE, '' ],
		];
	}

	public function test_get_input_data_returns_offending_data() {

		$bulk  = new Bulk( [ 'validator' => new GreaterThan( [ 'min' => 5 ] ) ] );
		$valid = $bulk->is_valid( [ 6, 7, 8, 3, 10 ] );
		$data  = $bulk->get_input_data();

		$this->assertFalse( $valid );
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'value', $data );
		$this->assertSame( $data[ 'value' ], 3 );
	}

	public function test_consecutive_calls() {

		$bulk = new Bulk( [ 'validator' => new GreaterThan( [ 'min' => 5 ] ) ] );

		$this->assertTrue( $bulk->is_valid( [ 6, 7, 8, 10 ] ) );
		$code = $bulk->get_error_code();
		$data = $bulk->get_input_data();

		$this->assertSame( '', $code );
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'value', $data );
		$this->assertSame( 10, $data[ 'value' ] );

		$this->assertFalse( $bulk->is_valid( [ 6, 7, 8, 2, 10 ] ) );
		$code = $bulk->get_error_code();
		$data = $bulk->get_input_data();

		$this->assertSame( ErrorLoggerInterface::NOT_GREATER, $code );
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'value', $data );
		$this->assertSame( 2, $data[ 'value' ] );
	}

}