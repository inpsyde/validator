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

use Inpsyde\Validator\Pool;
use Inpsyde\Validator\Error\ErrorLoggerInterface;
use Inpsyde\Validator\ExtendedValidatorInterface;
use Inpsyde\Validator\GreaterThan;
use Inpsyde\Validator\NotEmpty;
use Inpsyde\Validator\Url;
use Mockery;

/**
 * Class PoolTest
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
class PoolTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function test_constructor_needs_validator() {

		new Pool();
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

		$pool = Pool::with_validator( $validator->getMock() );

		$this->assertTrue( $pool->is_valid( [ 'foo' ] ) );
	}

	public function test_constructor_can_use_factory() {

		$validator       = Mockery::mock( ExtendedValidatorInterface::class );
		$validator_class = get_class( $validator );

		$pool             = new Pool( [ 'validator' => $validator_class ] );
		$input            = $pool->get_input_data();
		$validator_stored = $input[ 'validator' ];

		$this->assertInternalType( 'object', $validator_stored );
		$this->assertInstanceOf( $validator_class, $validator_stored );
	}

	public function test_is_valid_accepts_traversable_only() {

		$pool  = new Pool( [ 'validator' => Mockery::mock( ExtendedValidatorInterface::class ) ] );
		$valid = $pool->is_valid( '' );

		$this->assertFalse( $valid );
		$this->assertSame( ErrorLoggerInterface::INVALID_TYPE_NON_TRAVERSABLE, $pool->get_error_code() );
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

		$pool   = new Pool( [ 'validator' => $validator ] );
		$result = $pool->is_valid( $value );
		$expected_valid ? $this->assertTrue( $result ) : $this->assertFalse( $result );
		$this->assertSame( $error, $pool->get_error_code() );
	}

	public function is_valid_cases_provider() {

		return [
			[
				[ 2, 4, 6 ],
				new GreaterThan( [ 'min' => 1 ] ),
				TRUE,
				''
			],
			[
				[ 2, 4, 6 ],
				new GreaterThan( [ 'min' => 3 ] ),
				TRUE,
				''
			],
			[
				[ 1, 2, 'x' ],
				new NotEmpty(),
				TRUE,
				''
			],
			[
				[ '', NULL, FALSE ],
				new NotEmpty(),
				FALSE,
				ErrorLoggerInterface::IS_EMPTY
			],
			[
				[ 'http://example.com', 'example.com' ],
				new Url(),
				TRUE,
				''
			],
			[
				[ 'http://example.com', 'https://example.it' ],
				new Url(),
				TRUE,
				''
			],
			[
				[ ],
				new GreaterThan( [ 'min' => 1 ] ),
				FALSE,
				ErrorLoggerInterface::IS_EMPTY
			],
		];
	}

	public function test_consecutive_calls() {

		$pool = new Pool( [ 'validator' => new GreaterThan( [ 'min' => 5 ] ) ] );

		$this->assertTrue( $pool->is_valid( [ 6, 7, 8, 10 ] ) );
		$code = $pool->get_error_code();
		$data = $pool->get_input_data();

		$this->assertSame( '', $code );
		$this->assertInternalType( 'array', $data );
		$this->assertArrayHasKey( 'value', $data );
		$this->assertSame( 6, $data[ 'value' ] );

		$this->assertFalse( $pool->is_valid( [ 1, 2, 3, 4 ] ) );
		$code = $pool->get_error_code();
		$data = $pool->get_input_data();

		$this->assertSame( ErrorLoggerInterface::NOT_GREATER, $code );
		$this->assertInternalType( 'array', $data );
		$this->assertArrayHasKey( 'value', $data );
		$this->assertSame( 4, $data[ 'value' ] );
	}

}