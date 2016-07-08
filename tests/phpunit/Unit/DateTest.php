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

use Inpsyde\Validator;
use DateTime;
use DateTimeZone;
use DateTimeImmutable;
use stdClass;

/**
 * Class DateTest
 *
 * @author  Christian Brückner <chris@chrico.info>
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
class DateTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {

		date_default_timezone_set( 'Europe/Berlin' );
	}

	public function test_DateTimeImmutable() {

		$validator = new Validator\Date();
		$this->assertTrue( $validator->is_valid( new DateTimeImmutable() ) );
	}

	/**
	 * Ensures that the validator follows expected behavior
	 *
	 * @dataProvider provide__basic_data
	 */
	public function test_basic( $input, $format, $result ) {

		if ( $format !== NULL ) {
			$options = [ 'format' => $format ];
		} else {
			$options = [ ];
		}

		$validator = new Validator\Date( $options );
		$this->assertEquals(
			$result,
			$validator->is_valid( $input )
		);
	}

	/**
	 * @return array
	 */
	public function provide__basic_data() {

		return [
			//    date, format, is_valid
			[ '01.01.2007', NULL, TRUE ],
			[ '28.02.2007', NULL, TRUE ],
			[ '29.02.2007', NULL, FALSE ],
			[ '29.02.2008', NULL, TRUE ],
			[ '30.02.2007', NULL, FALSE ],
			[ '99.02.2007', NULL, FALSE ],
			[ '2007-02-99', 'Y-m-d', FALSE ],
			[ '99.99.9999', NULL, FALSE ],
			[ '9999-99-99', 'Y-m-d', FALSE ],
			[ 'Jan 1 2007', NULL, FALSE ],
			[ 'Jan 1 2007', 'M j Y', TRUE ],
			[ 'asdasda', NULL, FALSE ],
			[ 'sdgsdg', NULL, FALSE ],
			[ '2007-01-01something', NULL, FALSE ],
			[ 'something2007-01-01', NULL, FALSE ],
			[ '10.01.2008', 'd.m.Y', TRUE ],
			[ '01 2010', 'm Y', TRUE ],
			[ '2008/10/22', 'd/m/Y', FALSE ],
			[ '22/10/08', 'd/m/y', TRUE ],
			[ '22/10', 'd/m/Y', FALSE ],
			// time
			[ '2007-01-01T12:02:55Z', DateTime::ISO8601, TRUE ],
			[ '12:02:55', 'H:i:s', TRUE ],
			[ '25:02:55', 'H:i:s', FALSE ],
			// int
			[ 0, NULL, TRUE ],
			[ 1340677235, NULL, TRUE ],
			// 32bit version of php will convert this to double
			[ 999999999999, NULL, TRUE ],
			// double
			[ 12.12, NULL, FALSE ],
			// array
			[ [ '2012', '06', '25' ], 'Y-m-d', TRUE ],
			// 0012-06-25 is a valid date, if you want 2012, use 'y' instead of 'Y'
			[ [ '12', '06', '25' ], 'Y-m-d', TRUE ],
			[ [ '2012', '06', '33' ], 'Y-m-d', FALSE ],
			[ [ 1 => 1 ], NULL, FALSE ],
			// DateTime
			[ new DateTime( 'now', new DateTimeZone( 'Europe/Berlin' ) ), NULL, TRUE ],
			// invalid obj
			[ new stdClass(), NULL, FALSE ],
		];
	}

	/**
	 * Ensures that the validator can handle different manual date formats
	 *
	 * @dataProvider provide__manual_dates
	 */
	public function test_use_manual_format( $input, $format, $result ) {

		$options   = [ 'format' => $format ];
		$validator = new Validator\Date( $options );

		$this->assertEquals(
			$result,
			$validator->is_valid( $input )
		);
	}

	/**
	 * @return array
	 */
	public function provide__manual_dates() {

		return [
			//    date, format, is_valid
			"month_year_valid"         => [ '01 2010', 'm Y', TRUE ],
			"month_year_invalid"       => [ '21 06 2010', 'm Y', FALSE ],
			"day_month_year_valid"     => [ '22/10/08', 'd/m/Y', TRUE ],
			"day_month_year_invalid"   => [ '08/22/10', 'd/m/Y', FALSE ],
			"day_month_year_invalid_2" => [ '22/10', 'd/m/Y', FALSE ],
		];
	}

	/**
	 * Tests that error code is returned according to validation results and options.
	 */
	public function test_get_error_code() {

		$validator_bad_type = new Validator\Date();
		$validator_bad_type->is_valid( new stdClass() );
		$code_bad_type = $validator_bad_type->get_error_code();

		$validator_bad_format = new Validator\Date( [ 'format' => 'Y-d-m' ] );
		$validator_bad_format->is_valid( '2007-02-99' );
		$code_bad_format = $validator_bad_format->get_error_code();

		$validator_bad_date = new Validator\Date( [ 'format' => 'Y-d-m' ] );
		$validator_bad_date->is_valid( '' );
		$code_bad_date = $validator_bad_date->get_error_code();

		$this->assertSame( Validator\Error\ErrorLoggerInterface::INVALID_TYPE_NON_DATE, $code_bad_type );
		$this->assertSame( Validator\Error\ErrorLoggerInterface::INVALID_DATE_FORMAT, $code_bad_format );
		$this->assertSame( Validator\Error\ErrorLoggerInterface::INVALID_DATE, $code_bad_date );
	}

	/**
	 * Even if deprecated, we need to test get_error_messages() is backward compatible
	 */
	public function test_get_error_messages() {

		$validator = new Validator\Date( [ 'format' => 'Y-d-m' ] );
		$validator->is_valid( '' );
		$validator->is_valid( '2007-02-99' );

		// muted because triggers deprecation notices
		$messages = @$validator->get_error_messages();

		$this->assertInternalType( 'array', $messages );
		$this->assertCount( 2, $messages );
		$this->assertContains( 'does not appear to be a valid date', reset( $messages ) );
		$this->assertContains( 'does not fit the date format', end( $messages ) );
	}

	/**
	 * Tests that input data is returned according to validation results and options.
	 */
	public function test_get_input_data() {

		$validator = new Validator\Date();

		$validator->is_valid( '01.01.2007' );
		$input = $validator->get_input_data();

		$this->assertInternalType( 'array', $input );
		$this->assertArrayHasKey( 'value', $input );
		$this->assertSame( '01.01.2007', $input[ 'value' ] );

		$validator->is_valid( '01.01.2008' );

		$input = $validator->get_input_data();
		$this->assertSame( '01.01.2008', $input[ 'value' ] );
	}

}