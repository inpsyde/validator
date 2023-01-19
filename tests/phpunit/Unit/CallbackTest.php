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

/**
 * Class CallbackTest
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
class CallbackTest extends AbstractTestCase {

	public function test_constructor_needs_callback() {
        static::expectException(\InvalidArgumentException::class);
		new Callback( [ ] );
	}

	/**
	 * @param mixed    $value
	 * @param callable $validator
	 * @param bool     $expected_valid
	 *
	 * @dataProvider is_valid_cases_provider
	 */
	public function test_is_valid( $value, callable $validator, $expected_valid ) {

		$bulk = new Callback( [ 'callback' => $validator ] );

		$result = $bulk->is_valid( $value );
		$expected_valid ? $this->assertTrue( $result ) : $this->assertFalse( $result );
	}

	public function is_valid_cases_provider() {

		$inpsyde = function ( $value ) {

			return $value === 'Inpsyde';
		};

		return [
			[ 1, 'is_string', FALSE ],
			[ 'x', 'is_string', TRUE ],
			[ 1, 'is_int', TRUE ],
			[ 'x', 'is_int', FALSE ],
			[ ' Inpsyde ', $inpsyde, FALSE ],
			[ 'Inpsyde', $inpsyde, TRUE ],
		];
	}

}