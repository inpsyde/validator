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

use Inpsyde\Validator\Error\ErrorLoggerInterface;
use Inpsyde\Validator\Url;

/**
 * Class UrlTest
 *
 * @author  Christian Brückner <chris@chrico.info>
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
class UrlTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Ensures that the validator follows expected behavior
	 *
	 * @dataProvider provide__basic_data
	 *
	 * @param string $input
	 * @param bool   $expect
	 *
	 * @return void
	 */
	public function test_basic( $input, $expect = TRUE ) {

		$validator = new Url();

		$this->assertEquals(
			$expect,
			$validator->is_valid( $input )
		);
	}

	/**
	 * Tests that error code is returned according to validation results and options.
	 */
	public function test_get_error_code() {

		$validator = new Url( [ 'check_dns' => TRUE ] );

		$validator->is_valid( new \stdClass() );
		$code_non_string = $validator->get_error_code();

		$validator->is_valid( '' );
		$code_empty = $validator->get_error_code();

		$validator->is_valid( 'http:// .com' );
		$code_non_url = $validator->get_error_code();

		$validator->is_valid( 'http://mehmehmehmehmehmeh.ciaociao' );
		$code_no_dns = $validator->get_error_code();

		$this->assertSame( ErrorLoggerInterface::INVALID_TYPE_NON_STRING, $code_non_string );
		$this->assertSame( ErrorLoggerInterface::IS_EMPTY, $code_empty );
		$this->assertSame( ErrorLoggerInterface::NOT_URL, $code_non_url );
		$this->assertSame( ErrorLoggerInterface::INVALID_DNS, $code_no_dns );
	}

	/**
	 * Tests that input data is returned according to validation results and options.
	 */
	public function test_get_input_data() {

		$validator = new Url();

		$validator->is_valid( 'http://www.example.com' );
		$input = $validator->get_input_data();

		$this->assertInternalType( 'array', $input );
		$this->assertArrayHasKey( 'value', $input );
		$this->assertSame( 'http://www.example.com', $input[ 'value' ] );

		$validator->is_valid( 'meh' );

		$input = $validator->get_input_data();
		$this->assertSame( 'meh', $input[ 'value' ] );
	}

	/**
	 * @return array
	 */
	public function provide__basic_data() {

		return [
			// $input, $expect
			"http_www_domain"   => [ 'http://www.google.com', TRUE ],
			"https_www_domain"  => [ 'http://www.google.com', TRUE ],
			"http_domain"       => [ 'http://google.com', TRUE ],
			"https_domain"      => [ 'https://google.com', TRUE ],
			"https_port_domain" => [ 'https://google.com:80', TRUE ],
			"ipv4"              => [ 'http://127.0.0.1/', TRUE ],
			"ipv4_port"         => [ 'http://127.0.0.1:80/', TRUE ],
			"ipv6"              => [ 'http://[1:2:3::4:5:6:7]/', TRUE ],
			"ipv6_short"        => [ 'http://[::1]/', TRUE ],
			"ipv6_port"         => [ 'http://[::1]:80/', TRUE ],
			"user_pass_domain"  => [ 'http://username:password@inpsyde.com', TRUE ],
			"get"               => [ 'http://inpsyde.com?s=wordpress', TRUE ],
			"fragment_1"        => [ 'http://inpsyde.com#', TRUE ],
			"fragment_2"        => [ 'http://inpsyde.com#fragment', TRUE ],
			"fragment_3"        => [ 'http://inpsyde.com/#fragment', TRUE ],
		];
	}

}