<?php

namespace Inpsyde\Validator;

/**
 * Class ValidatorFactory
 *
 * @package Inpsyde\Validator
 */
class ValidatorFactory {

	/**
	 * @var array
	 */
	private $classes = [
		'ArrayValue'  => ArrayValue::class,
		'Between'     => Between::class,
		'Date'        => Date::class,
		'GreaterThan' => GreaterThan::class,
		'InArray'     => InArray::class,
		'LessThan'    => LessThan::class,
		'NotEmpty'    => NotEmpty::class,
		'RegEx'       => RegEx::class,
		'Url'         => Url::class,
	];

	/**
	 * Creates and returns a new validator instance of the given type.
	 *
	 * @param       $type
	 * @param array $properties
	 *
	 * @throws Exception\InvalidArgumentException if validator of given $type is not found.
	 *
	 * @return ValidatorInterface
	 */
	public function create( $type, array $properties = [ ] ) {

		$type = (string) $type;

		if ( isset( $this->classes[ $type ] ) ) {
			$class = $this->classes[ $type ];

			return new $class( $properties );
		} else if ( class_exists( $type ) ) {
			$class = new $type( $properties );
			if ( $class instanceof ValidatorInterface ) {

				return $class;
			}
		}

		throw new Exception\InvalidArgumentException(
			sprintf(
				'The given class <code>%s</code> does not exists.',
				$type
			)
		);

	}
}