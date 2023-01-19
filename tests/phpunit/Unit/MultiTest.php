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
use Inpsyde\Validator\Multi;
use Inpsyde\Validator\Error\ErrorLoggerInterface;
use Inpsyde\Validator\ExtendedValidatorInterface;
use Inpsyde\Validator\NotEmpty;
use Mockery;

class MultiTest extends AbstractTestCase {

	public function test_constructor_can_use_factory() {

		$validator = Mockery::mock( ExtendedValidatorInterface::class );

		static::assertInstanceOf(Multi::class, new Multi( [ ], [ get_class( $validator ) ] ));

	}

    /**
     * @param mixed $value
     * @param array $options
     * @param ExtendedValidatorInterface[] $validators
     * @param bool $expected_valid
     * @param array $errors
     *
     * @dataProvider is_valid_cases_provider
     */
	public function test_is_valid($value, array $options, array $validators, $expected_valid, array $errors = [ ]) {

		$multi   = new Multi( $options, $validators );
		$result  = $multi->is_valid( $value );
		$message = sprintf( "Failing for %s: got %s.", $value, $expected_valid ? 'false' : 'true' );
		$expected_valid ? $this->assertTrue( $result, $message ) : $this->assertFalse( $result, $message );
		$this->assertSame( $errors, $multi->get_error_codes() );
	}

    public function test_stop_on_failure() {
        $failure = new Callback(
            [
                'callback' => function () {
                    return FALSE;
                }
            ]
        );
        $greater_than_two = new GreaterThan( [ 'min' => 2 ] );

        $value = 1;
        $validators = [ $failure, $greater_than_two ];

        $multi = new Multi( [ 'stop_on_failure' => FALSE ], $validators );

        $stopping_multi = $multi->stop_on_failure();

        $this->assertFalse($stopping_multi->is_valid($value));
        $this->assertSame( [ ErrorLoggerInterface::CUSTOM_ERROR ], $stopping_multi->get_error_codes() );

        $this->assertFalse($multi->is_valid($value));
        $this->assertSame( [ ErrorLoggerInterface::CUSTOM_ERROR, ErrorLoggerInterface::NOT_GREATER ], $multi->get_error_codes() );
    }

	public function is_valid_cases_provider() {

		$success = new Callback(
			[
				'callback' => function () {
					return TRUE;
				}
			]
		);

		$failure = new Callback(
			[
				'callback' => function () {
					return FALSE;
				}
			]
		);

		$not_empty = new NotEmpty();

        $greater_than_two = new GreaterThan( [ 'min' => 2 ] );

        return [
            [
                3,
                [ ],
                [ $success, $greater_than_two ],
                TRUE,
                [ ]
            ],
            [
                3,
                [ 'stop_on_failure' => TRUE ],
                [ $success, $greater_than_two ],
                TRUE,
                [ ]
            ],
            [
                1,
                [ 'stop_on_failure' => FALSE ],
                [ $failure, $greater_than_two ],
                FALSE,
                [ ErrorLoggerInterface::CUSTOM_ERROR, ErrorLoggerInterface::NOT_GREATER ]
            ],
            [
                1,
                [ 'stop_on_failure' => TRUE ],
                [ $failure, $greater_than_two ],
                FALSE,
                [ ErrorLoggerInterface::CUSTOM_ERROR ]
            ],
            [
                '',
                [ ],
                [ $not_empty, $failure ],
                FALSE,
                [ ErrorLoggerInterface::IS_EMPTY, ErrorLoggerInterface::CUSTOM_ERROR ]
            ],
            [
                '',
                [ ],
                [ $not_empty ],
                FALSE,
                [ ErrorLoggerInterface::IS_EMPTY ]
            ],
            [
                '',
                [ ],
                [ ],
                TRUE,
                [ ]
            ],
        ];
    }

}
