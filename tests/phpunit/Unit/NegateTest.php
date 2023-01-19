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

use Inpsyde\Validator\Callback;
use Inpsyde\Validator\Negate;
use Inpsyde\Validator\ExtendedValidatorInterface;
use Mockery;

/**
 * Class NegateTest
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
class NegateTest extends AbstractTestCase {

	public function test_constructor_needs_validator() {
        static::expectException(\InvalidArgumentException::class);
		new Negate();
	}

	public function test_with_validator() {

		$validator = Mockery::mock( ExtendedValidatorInterface::class )
			->shouldReceive( 'is_valid' )
			->with( 'foo' )
			->once()
			->andReturn( FALSE )
			->getMock()
			->shouldReceive( 'get_input_data' )
			->andReturn( [ 'value' => 'foo' ] )
			->getMock()
			->shouldReceive( 'get_error_code' )
			->andReturn( '' );

		$negate = Negate::with_validator( $validator->getMock() );

		$this->assertTrue( $negate->is_valid( 'foo' ) );
	}

	public function test_constructor_can_use_factory() {

		$validator       = Mockery::mock( ExtendedValidatorInterface::class );
		$validator_class = get_class( $validator );

		$negate           = new Negate( [ 'validator' => $validator_class ] );
		$input            = $negate->get_input_data();
		$validator_stored = $input[ 'validator' ];

		$this->assertIsObject( $validator_stored );
		$this->assertInstanceOf( $validator_class, $validator_stored );
	}

	/**
	 * @param mixed                      $value
	 * @param ExtendedValidatorInterface $validator
	 * @param bool                       $expected_valid
	 *
	 * @dataProvider is_valid_cases_provider
	 */
	public function test_is_valid( $value, $validator, $expected_valid ) {

		$negate = new Negate( [ 'validator' => $validator ] );
		$result = $negate->is_valid( $value );

		$expected_valid
			? $this->assertTrue( $result, serialize( $value ) )
			: $this->assertFalse( $result, serialize( $value ) );
	}

	public function is_valid_cases_provider() {

		$true = Callback::with_callback(
			function () {

				return TRUE;
			}
		);

		$false = Callback::with_callback(
			function () {

				return FALSE;
			}
		);

		return [
			[ TRUE, $true, FALSE ],
			[ FALSE, $false, TRUE ],
		];
	}

}