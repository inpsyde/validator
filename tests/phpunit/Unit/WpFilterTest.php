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

use Brain\Monkey\Filters;
use Inpsyde\Validator\WpFilter;
use function Brain\Monkey\Filters\expectApplied;

/**
 * Class WpFilterTest
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
class WpFilterTest extends AbstractTestCase {

	public function test_filter_is_required() {
        static::expectException(\InvalidArgumentException::class);
		new WpFilter();
	}

	public function test_filter_need_to_be_string() {
        static::expectException(\InvalidArgumentException::class);
		new WpFilter( [ 'type' => TRUE ] );
	}

	public function test_is_valid_no_filters() {

		$validator = new WpFilter( [ 'filter' => 'test-filter' ] );

		$this->assertFalse( $validator->is_valid( FALSE ) );
		$this->assertTrue( $validator->is_valid( TRUE ) );
	}

	public function test_is_valid_cast_to_bool_return_value() {

		expectApplied( 'test-filter-return-bar' )
			->once()
			->with( 'foo' )
			->andReturn( 'bar', 'true' );

		expectApplied( 'test-filter-return-true' )
			->once()
			->with( 'foo' )
			->andReturn( 'true' );

		expectApplied( 'test-filter-return-null' )
			->once()
			->with( 'foo' )
			->andReturnNull();

		$validator1 = new WpFilter( [ 'filter' => 'test-filter-return-bar' ] );
		$validator2 = new WpFilter( [ 'filter' => 'test-filter-return-true' ] );
		$validator3 = new WpFilter( [ 'filter' => 'test-filter-return-null' ] );

		$this->assertFalse( $validator1->is_valid( 'foo' ) );
		$this->assertTrue( $validator2->is_valid( 'foo' ) );
		$this->assertFalse( $validator3->is_valid( 'foo' ) );
	}
}