<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the inpsyde-validator package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inpsyde\Validator;

/**
 * Class ArrayValue
 *
 * @author     Christian Brückner <chris@chrico.info>
 * @author     Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package    inpsyde-validator
 * @license    http://opensource.org/licenses/MIT MIT
 * @deprecated Use DataValidator instead
 */
class ArrayValue implements ExtendedValidatorInterface {

	use ValidatorDataGetterTrait;

	/**
	 * @deprecated Error codes are now defined in Error\ErrorLoggerInterface
	 */
	const INVALID_TYPE = Error\ErrorLoggerInterface::INVALID_TYPE_NON_TRAVERSABLE;

	/**
	 * @var array
	 * @deprecated
	 */
	protected $message_templates = [
		Error\ErrorLoggerInterface::INVALID_TYPE_NON_TRAVERSABLE => 'The given value <code>%value</code> is not an array or implements Traversable.'
	];

	/**
	 * @var array
	 * @deprecated
	 */
	protected $error_messages = [ ];

	/**
	 * Contains a group of validators.
	 *
	 * @var ValidatorInterface[]
	 */
	private $validators = [ ];

	/**
	 * Contains validators grouped by an array key.
	 *
	 * @var ValidatorInterface[]
	 */
	private $validators_by_key = [ ];

	/**
	 * Adding validators mapped for all array values.
	 *
	 * @param ValidatorInterface $validator
	 *
	 * @return ArrayValue
	 *
	 * @deprecated Please use DataValidator::add_validator_by_key() instead
	 */
	public function add_validator( ValidatorInterface $validator ) {

		$this->validators[] = $validator;

		return $this;
	}

	/**
	 * Adding a validator grouped by array key.
	 *
	 * @throws Exception\InvalidArgumentException if type of $key is not scalar.
	 *
	 * @param ValidatorInterface $validator
	 * @param                    $key
	 *
	 * @return ArrayValue
	 *
	 * @deprecated Please use DataValidator::add_validator_by_key() instead
	 */
	public function add_validator_by_key( ValidatorInterface $validator, $key ) {

		if ( ! is_scalar( $key ) ) {
			throw new Exception\InvalidArgumentException( 'key should be a scalar value.' );
		}

		$key = (string) $key;

		if ( ! isset ( $this->validators_by_key[ $key ] ) ) {
			$this->validators_by_key[ $key ] = [ ];
		}

		$this->validators_by_key[ $key ][] = $validator;

		return $this;
	}

	/**
	 * @deprecated Please use DataValidator::is_valid() instead
	 *
	 * @param array|\Traversable $values
	 *
	 * @return bool
	 */
	public function is_valid( $values ) {

		$this->input_data = [ 'value' => $values ];

		if ( ! is_array( $values ) && ! $values instanceof \Traversable ) {
			$this->error_code = Error\ErrorLoggerInterface::INVALID_TYPE_NON_TRAVERSABLE;

			return FALSE;
		}

		if ( ! $this->validate( $values ) ) {
			return FALSE;
		}

		if ( ! $this->validate_by_key( $values ) ) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * @deprecated Messages are now managed via the `Error\WordPressErrorLogger` class.
	 *
	 * @return array
	 */
	public function get_error_messages() {

		return $this->error_messages;
	}

	/**
	 * Validates all values.
	 *
	 * @param $values
	 *
	 * @return bool TRUE|FALSE
	 */
	private function validate( $values ) {

		$is_valid = TRUE;

		foreach ( $values as $key => $value ) {

			if ( ! is_scalar( $value ) ) {
				continue;
			}

			foreach ( $this->validators as $validator ) {
				$is_valid = $validator->is_valid( $value );

				if ( ! $is_valid ) {
					$this->error_messages[ $key ] = $validator->get_error_messages();
					break;
				}
			}

			if ( ! $is_valid ) {
				break;
			}
		}

		return $is_valid;
	}

	/**
	 * Validates all values by array-key.
	 *
	 * TODO: Add option to validate recursive through an array with RecursiveArrayIterator.
	 *
	 * @param   mixed $values
	 *
	 * @return  bool TRUE|FALSE
	 *
	 * @deprecated
	 */
	protected function validate_by_key( $values ) {

		$is_valid = TRUE;

		if ( count( $this->validators_by_key ) < 1 ) {

			return $is_valid;
		}

		foreach ( $values as $key => $value ) {
			if ( ! is_scalar( $value ) || ! isset( $this->validators_by_key[ $key ] ) ) {
				continue;
			}

			/** @var $validators ValidatorInterface[] */
			$validators = $this->validators_by_key[ $key ];
			foreach ( $validators as $validator ) {
				$is_valid = $validator->is_valid( $value );

				if ( ! $is_valid ) {
					$this->error_messages[ $key ] = $validator->get_error_messages();
					break;
				}
			}

			if ( ! $is_valid ) {
				break;
			}
		}

		return $is_valid;
	}
}