<?php

namespace Inpsyde\Tests\Unit\Validator;

class AbstractValidatorTest extends \PHPUnit_Framework_TestCase {

	public function test_basic() {

		$this->assertInstanceOf( '\Inpsyde\Validator\ValidatorInterface', $this->create_stub() );
	}

	/**
	 * Basic test for error messages if nothing is validated.
	 */
	public function test_get_options() {

		/** @var \Inpsyde\Validator\AbstractValidator $stub */
		$stub = $this->create_stub();
		$this->assertEquals( [ ], $stub->get_options() );
	}

	/**
	 * Basic test for implementation of "get_message_template()"
	 */
	public function test_overwrite_options() {

		$expected_options = [ 'key' => 'value' ];

		/** @var \Inpsyde\Validator\AbstractValidator $stub */
		$stub = $this->create_stub( [ $expected_options ] );
		$this->assertEquals( $expected_options, $stub->get_options() );
	}

	/**
	 * Basic test for error messages if nothing is validated.
	 */
	public function test_get_error_messages() {

		/** @var \Inpsyde\Validator\AbstractValidator $stub */
		$stub = $this->create_stub();
		$this->assertEquals( [ ], $stub->get_error_messages() );
	}

	/**
	 * Basic test for implementation of "get_message_template()"
	 */
	public function test_message_templates() {

		/** @var \Inpsyde\Validator\AbstractValidator $stub */
		$stub = $this->create_stub();
		$this->assertEquals( [ ], $stub->get_message_templates() );
	}

	/**
	 * Basic test for implementation of "get_message_template()"
	 */
	public function test_custom_message_templates() {

		$expected_message_template = [ 'key' => 'value' ];

		/** @var \Inpsyde\Validator\AbstractValidator $stub */
		$stub = $this->create_stub( [ [ ], $expected_message_template ] );
		$this->assertEquals( $expected_message_template, $stub->get_message_templates() );
	}

	/**
	 * Basic test for implementation of "is_valid()".
	 */
	public function test_is_valid() {

		$value = 'some value';

		/** @var \Inpsyde\Validator\ValidatorInterface */
		$stub = $this->create_stub();
		$stub->expects( $this->any() )
		     ->method( 'is_valid' )
		     ->with( $value )
		     ->will( $this->returnValue( FALSE ) );

		$this->assertFalse( $stub->is_valid( $value ) );
	}

	/**
	 * Returns a new Mock of the AbstractValidator.
	 *
	 * @param array $args
	 *
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	private function create_stub( $args = [ ] ) {

		return $this->getMockForAbstractClass( 'Inpsyde\Validator\AbstractValidator', $args );
	}

}