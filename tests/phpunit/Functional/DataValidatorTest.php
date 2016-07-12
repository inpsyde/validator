<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the inpsyde-validator package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inpsyde\Validator\Tests\Functional;

use Inpsyde\Validator\DataValidator;
use Inpsyde\Validator\Date;
use Inpsyde\Validator\Error\ErrorLoggerInterface;
use Inpsyde\Validator\GreaterThan;
use Inpsyde\Validator\LessThan;
use Inpsyde\Validator\Multi;
use Inpsyde\Validator\NotEmpty;
use Inpsyde\Validator\RegEx;

/**
 * Class DataValidatorTest
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
class DataValidatorTest extends \PHPUnit_Framework_TestCase {

	public function test_is_valid() {

		$validator = new DataValidator();

		$multi_1 = Multi::with_validators(
			[ 'validator' => 'greater-than', 'options' => [ 'min' => 8, 'max' => 9 ] ],
			new LessThan( [ 'max' => 9 ] )
		);
		$multi_1->stop_on_failure();

		$multi_2 = Multi::with_validators( new GreaterThan( [ 'min' => 10 ] ), new LessThan( [ 'max' => 20 ] ) );

		$validator
			->add_validator_with_message( new NotEmpty(), 'No data value should be empty.' )
			->add_validator_by_key( $multi_1, 'an_integer' )
			->add_validator_by_key( new RegEx( [ 'pattern' => '/Hello/' ] ), 'a_string' )
			->add_validator_by_key( new RegEx( [ 'pattern' => '/Hello/' ] ), 'an_empty_string' )
			->add_validator_by_key(
				$multi_2,
				[ 'key' => 'a_float', 'label' => 'Float value' ],
				'"%key%" should be a float > 10 and < 20. "%value%" is wrong.'
			)
			->add_validator_by_key( new Date( [ 'format' => 'd/m/Y' ] ), 'a_date' )
			->add_validator_by_key(
				new Date( [ 'format' => 'd/m/Y' ] ),
				[ 'key' => 'a_bad_date', 'label' => 'Some Bad Date' ]
			);

		$data = [
			'an_integer'      => 10,
			'a_string'        => 'Hello World',
			'an_empty_string' => '',
			'a_float'         => 5.5,
			'a_date'          => '30/09/1982',
			'a_bad_date'      => '99/09/1982'
		];

		$expected_codes = [
			ErrorLoggerInterface::IS_EMPTY,
			ErrorLoggerInterface::NOT_LESS,
			ErrorLoggerInterface::NOT_MATCH,
			ErrorLoggerInterface::NOT_GREATER,
			ErrorLoggerInterface::INVALID_DATE_FORMAT,
		];

		$expected_messages = [
			'No data value should be empty.',
			'<code>an_integer</code>: The input <code>(integer) 10</code> is not less than <code>(integer) 9</code>.',
			'<code>an_empty_string</code>: The input does not match against pattern <code>/Hello/</code>.',
			'"Float value" should be a float > 10 and < 20. "(double) 5.5" is wrong.',
			'<code>Some Bad Date</code>: The input <code>99/09/1982</code> does not fit the date format <code>d/m/Y</code>.',
		];

		$this->assertFalse( $validator->is_valid( $data ) );
		$this->assertSame( $expected_codes, $validator->get_error_codes() );
		$this->assertSame( $expected_messages, $validator->get_error_messages() );
	}

}