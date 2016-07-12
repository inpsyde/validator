<?php

namespace Inpsyde\Validator\Tests\Unit;

use Inpsyde\Validator\Date;
use Inpsyde\Validator\GreaterThan;
use Inpsyde\Validator\Tests\Stub\AlwaysFalseWithInvalidMessageValidator;
use Inpsyde\Validator\ValidatorFactory;
use Inpsyde\Validator\ValidatorInterface;

/**
 * Class ValidatorFactoryTest
 *
 * @author  Christian Brückner <chris@chrico.info>
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
class ValidatorFactoryTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Ensures that the validator follows expected behavior
	 *
	 * @dataProvider provide__basic
	 *
	 * @param string|ValidatorInterface $identifier
	 * @param string                    $expected
	 *
	 * @return void
	 */
	public function test_basic( $identifier, $expected ) {

		$factory = new ValidatorFactory();

		$validator = $factory->create( $identifier );

		$this->assertInternalType( 'object', $validator );
		$this->assertInstanceOf( $expected, $validator );

	}

	/**
	 * Test if Factory creates external classes which are implementing the ValidatorInterface
	 */
	public function test_external_validator() {

		$factory = new ValidatorFactory();
		$factory->create( AlwaysFalseWithInvalidMessageValidator::class );
	}

	/**
	 * Test if Factory throws an exception if validator is undefined.
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function test_unknown_validator() {

		$factory = new ValidatorFactory();
		$factory->create( 'some invalid class name' );
	}

	/**
	 * Test if Factory creates an object and set given properties when receive a validator instance as argument
	 */
	public function test_object_change_properties() {

		$factory = new ValidatorFactory();

		$validator = $factory->create( new GreaterThan(), [ 'min' => 100 ] );
		$data      = $validator->get_input_data();

		$this->assertInternalType( 'object', $validator );
		$this->assertInstanceOf( GreaterThan::class, $validator );
		$this->assertArrayHasKey( 'min', $data );
		$this->assertSame( 100, $data[ 'min' ] );
	}

	/**
	 * @return array
	 */
	public function provide__basic() {

		return [
			'simple'              => [ 'greater_than', GreaterThan::class ],
			'different_case'      => [ 'GreAter_ThaN', GreaterThan::class ],
			'different_separator' => [ 'GreAter~_~ThaN', GreaterThan::class ],
			'spaces'              => [ 'greater  than', GreaterThan::class ],
			'camel_case'          => [ 'greaterThan', GreaterThan::class ],
			'title_case'          => [ 'GreaterThan', GreaterThan::class ],
			'object'              => [ new GreaterThan(), GreaterThan::class ],
			'no_namespace'        => [ 'Date', Date::class ],
			'to_trim'             => [ ' greater than ', GreaterThan::class ],
		];
	}
}