<?php
namespace Inpsyde\Validator;

/**
 * Interface ValidatorInterface
 *
 * @package Inpsyde\Validator
 */
interface ValidatorInterface {

	/**
	 * Returns true if and only if $value meets the validation requirements
	 *
	 * If $value fails validation, then this method returns false, and
	 * get_error_messages() will return an array of messages that explain why the
	 * validation failed.
	 *
	 * @param   mixed $value
	 *
	 * @return  bool $is_valid
	 */
	public function is_valid( $value );

	/**
	 * Returns a messages that explain why the most recent is_valid()
	 * call returned false.
	 *
	 * If is_valid() was never called or if the most recent is_valid() call
	 * returned true, then this method returns an empty string.
	 *
	 * @return  array
	 */
	public function get_error_messages();

}