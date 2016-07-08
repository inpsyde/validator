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
 * Class ValidatorFactory
 *
 * @author  Christian Brückner <chris@chrico.info>
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
class ValidatorFactory {

	/**
	 * @var array
	 */
	private static $classes_map = [
		'array_value'  => ArrayValue::class,
		'between'      => Between::class,
		'date'         => Date::class,
		'greater_than' => GreaterThan::class,
		'in_array'     => InArray::class,
		'less_than'    => LessThan::class,
		'multi'        => Multi::class,
		'not_empty'    => NotEmpty::class,
		'regex'        => RegEx::class,
		'url'          => Url::class,
	];

	/**
	 * Creates and returns a new validator instance of the given type.
	 *
	 * @param string|ValidatorInterface $type
	 * @param array                     $properties
	 *
	 * @throws Exception\InvalidArgumentException if validator of given $type is not found.
	 *
	 * @return ValidatorInterface|ErrorAwareInterface
	 */
	public function create( $type, array $properties = [ ] ) {

		// If type is already an instance of validator, and no properties provided, we just return it as is
		if ( $type instanceof ValidatorInterface && ! $properties ) {
			return $type;
		}

		// If an instance of validator is given alongside some properties, we extract the class so a new instance
		// will be created with given properties
		if ( $type instanceof ValidatorInterface ) {
			$type = get_class( $type );
		}

		// From now on, we expect a string, if not, let's just throw an exception
		if ( ! is_string( $type ) ) {
			throw new Exception\InvalidArgumentException(
				sprintf( 'Validator identifier must be in a string, %s given.', gettype( $type ) )
			);
		}

		// If `$type` is the fully qualified name of a validator class, just use it
		if ( is_subclass_of( $type, ValidatorInterface::class, TRUE ) ) {
			return new $type( $properties );
		}

		// If name is fine, but namespace is missing, let's just add it and instantiate
		if ( is_subclass_of( __NAMESPACE__ . '\\' . $type, ValidatorInterface::class, TRUE ) ) {
			$class = __NAMESPACE__ . '\\' . $type;

			return new $class( $properties );
		}

		$type = trim( $type );

		// We accept case-insensitive types, e.g. 'greater_than', 'Greater_Than', 'GREATER_THAN'

		$lower_case_type = strtolower( $type );

		if ( isset( self::$classes_map[ $lower_case_type ] ) ) {
			$class = self::$classes_map[ $lower_case_type ];

			return new $class( $properties );
		}

		// We also accept alternative version of identifier:
		// - TitleCased: 'GreaterThan'
		// - camelCased: 'greaterThan'
		// - separated with any character and case insensitive: 'greater-than', 'greater~than', 'Greater Than'...

		$alt_types[] = strtolower( preg_replace( [ '/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/' ], '$1_$2', $type ) );
		$alt_types[] = preg_replace( '/[^a-z]+/', '_', $lower_case_type );

		foreach ( $alt_types as $alt_type ) {
			if ( isset( self::$classes_map[ $alt_type ] ) ) {
				$class = self::$classes_map[ $alt_type ];

				return new $class( $properties );
			}
		}

		throw new Exception\InvalidArgumentException(
			sprintf(
				'%s is not an accepted validator identifier for %s.',
				$type,
				__METHOD__
			)
		);

	}
}