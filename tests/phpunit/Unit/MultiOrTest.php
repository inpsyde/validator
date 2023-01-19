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
use Inpsyde\Validator\GreaterThan;
use Inpsyde\Validator\MultiOr;
use Inpsyde\Validator\Error\ErrorLoggerInterface;
use Inpsyde\Validator\ExtendedValidatorInterface;
use Inpsyde\Validator\NotEmpty;
use Inpsyde\Validator\ValidatorInterface;
use Mockery;

/**
 * Class MultiOrTest
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
class MultiOrTest extends AbstractTestCase {

	public function test_constructor_can_use_factory() {

		$validator = Mockery::mock( ExtendedValidatorInterface::class );

		static::assertInstanceOf(ValidatorInterface::class, new MultiOr( [ ], [ get_class( $validator ) ] ));

	}

	/**
	 * @param mixed $value
	 * @param array $validators
	 * @param bool  $expected_valid
	 * @param array $errors
	 *
	 * @dataProvider is_valid_cases_provider
	 */
	public function test_is_valid( $value, array $validators, $expected_valid, array $errors = [ ] ) {

		$multi   = new MultiOr( [ 'validators' => $validators ] );
		$result  = $multi->is_valid( $value );
		$message = sprintf( "Failing for %s: got %s.", $value, $expected_valid ? 'false' : 'true' );
		$expected_valid ? $this->assertTrue( $result, $message ) : $this->assertFalse( $result, $message );
		$this->assertSame( $errors, $multi->get_error_codes() );
	}

	public function is_valid_cases_provider() {

		$true = new Callback(
			[
				'callback' => function () {

					return TRUE;
				}
			]
		);

		$false = new Callback(
			[
				'callback' => function () {

					return FALSE;
				}
			]
		);

		$not_empty = new NotEmpty();

		$greater = new GreaterThan( [ 'min' => 2 ] );

		return [
			[
				3,
				[ $true, $false, $not_empty, $greater ],
				TRUE,
				[ ]
			],
			[
				1,
				[ $true, $false, $not_empty, $greater ],
				TRUE,
				[ ]
			],
			[
				12,
				[ $not_empty, $false, $false ],
				TRUE,
				[ ]
			],
			[
				1,
				[ $false, $greater ],
				FALSE,
				[ ErrorLoggerInterface::CUSTOM_ERROR, ErrorLoggerInterface::NOT_GREATER ]
			],
			[
				'',
				[ $not_empty, $false ],
				FALSE,
				[ ErrorLoggerInterface::IS_EMPTY, ErrorLoggerInterface::CUSTOM_ERROR ]
			],
			[
				'',
				[ $not_empty ],
				FALSE,
				[ ErrorLoggerInterface::IS_EMPTY ]
			],
			[
				'',
				[ ],
				TRUE,
				[ ]
			],
		];
	}

}