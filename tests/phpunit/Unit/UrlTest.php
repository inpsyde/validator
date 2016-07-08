<?php

namespace Inpsyde\Validator\Tests\Unit;

use Inpsyde\Validator\Url;

/**
 * Class UrlTest
 *
 * @package Inpsyde\Validator\Tests\Unit
 */
class UrlTest extends \PHPUnit_Framework_TestCase {

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

}