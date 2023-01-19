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

use Inpsyde\Validator\Tests\Stub\AlwaysFalseWithInvalidMessageValidator;

/**
 * Class AbstractValidatorTest
 *
 * @package Inpsyde\Validator\Tests\Unit
 */
class AbstractValidatorTest extends AbstractTestCase {

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
		$this->assertEquals( [ ], @$stub->get_error_messages() );
	}

	/**
	 * Basic test with Stub "AlwaysFalseValidator" which tests the creation of error_messages.
	 */
	public function test_error_message_creation() {

		$validator = new AlwaysFalseWithInvalidMessageValidator();
		$validator->is_valid( '' );

		$this->assertNotEmpty( @$validator->get_error_messages() );
	}

	/**
	 * Test if the placeholder %value% and key's in $options are replaced in error message.
	 */
	public function test_overwrite_error_message() {

		$message_template = 'replace value "%value%" and option "key" with value: "%key%" ';

		$expected_value        = 'value';
		$expected_option_value = 'option value';

		$expected_error = str_replace(
			[ '%value%', '%key%' ],
			[ $expected_value, $expected_option_value ],
			$message_template
		);

		$options          = [
			'key' => $expected_option_value
		];
		$message_template = [
			AlwaysFalseWithInvalidMessageValidator::INVALID => $message_template
		];

		$validator = new AlwaysFalseWithInvalidMessageValidator( $options, $message_template );
		$validator->is_valid( $expected_value );

		$this->assertEquals( [ $expected_error ], @$validator->get_error_messages() );
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