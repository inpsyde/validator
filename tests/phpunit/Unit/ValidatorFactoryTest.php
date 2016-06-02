<?php

namespace Inpsyde\Validator\Tests\Unit;

use Inpsyde\Validator\ArrayValue;
use Inpsyde\Validator\ValidatorFactory;

class ValidatorFactoryTest extends \PHPUnit_Framework_TestCase {

	public function test_basic() {

		$expected = '\Inpsyde\Validator\ValidatorInterface';
		$factory  = new ValidatorFactory();

		$this->assertInstanceOf( $expected, $factory->create( 'ArrayValue' ) );

	}

	/**
	 * Test if Factory creates external classes which are implementing the ValidatorInterface
	 */
	public function test_external_validator() {

		$factory = new ValidatorFactory();
		$factory->create( Fake\AlwaysFalseWithInvalidMessageValidator::class );
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
}