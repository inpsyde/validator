<?php
namespace Inpsyde\Validator;

/**
 * Class ArrayValueValidator
 *
 * @package Inpsyde\Validator
 */
class ArrayValueValidator extends AbstractValidator {

	/**
	 * Contains a group of validators.
	 *
	 * @var ValidatorInterface[]
	 */
	private $validators = [ ];

	/**
	 * Adding validators mapped to an array key.
	 *
	 * @param string             $key
	 * @param ValidatorInterface $validator
	 * @param bool               $break_on_failure
	 *
	 * @return void
	 */
	public function add_validator( $key, ValidatorInterface $validator, $break_on_failure = FALSE ) {

		if ( ! array_key_exists( $key, $this->validators ) ) {
			$this->validators[ $key ] = [ ];
		}
		$this->validators[ $key ][] = [
			'instance'         => $validator,
			'break_on_failure' => (bool) $break_on_failure
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_valid( $value ) {

		$is_valid = TRUE;
		foreach ( $value as $key => $v ) {
			if ( ! $this->validate( $key, $v ) ) {
				$is_valid = FALSE;
			}
		}

		return $is_valid;
	}

	/**
	 * Validating the given key and value of array.
	 *
	 * @param   string $key
	 * @param   mixed  $value
	 *
	 * @return  bool $is_valid
	 */
	protected function validate( $key, $value ) {

		$is_valid = TRUE;
		if ( is_array( $value ) ) {
			foreach ( $value as $new_key => $new_value ) {
				$is_valid = $this->validate( $new_key, $new_value );
			}
		}
		if ( array_key_exists( $key, $this->validators ) ) {
			/** @var ValidatorInterface[] $validators */
			$validators = $this->validators[ $key ];
			$is_valid   = $this->do_validate( $value, $validators );
		}

		return $is_valid;
	}

	/**
	 * @param   mixed                $value
	 * @param   ValidatorInterface[] $validators
	 *
	 * @return  bool $is_valid
	 */
	protected function do_validate( $value, $validators ) {

		$is_valid = TRUE;
		foreach ( $validators as $element ) {

			/** @var \Inpsyde\Validator\ValidatorInterface $validator */
			$validator = $element[ 'instance' ];
			if ( $validator->is_valid( $value ) ) {
				continue;
			}

			$is_valid = FALSE;
			if ( $element[ 'break_on_failure' ] ) {
				break;
			}
		}

		return $is_valid;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_error_messages() {

		$errors = [ ];
		foreach ( $this->validators as $elements ) {
			foreach ( $elements as $element ) {

				/** @var \Inpsyde\Validator\ValidatorInterface $validator */
				$validator = $element[ 'instance' ];
				foreach ( $validator->get_error_messages() as $message ) {
					$errors[] = $message;
				}

			}
		}

		return $errors;
	}

}