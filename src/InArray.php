<?php
namespace Inpsyde\Validator;

/**
 * Class InArray
 *
 * @package Inpsyde\Validator
 */
class InArray extends AbstractValidator {

	const NOT_IN_ARRAY = 'notInArray';

	/**
	 * @var array
	 */
	protected $message_templates = [
		self::NOT_IN_ARRAY => "The input <code>%value%</code> is not in the haystack: <code>%haystack%</code>.",
	];

	/**
	 * @var array
	 */
	protected $options = [
		'strict'   => TRUE,
		'haystack' => []
	];

	/**
	 * {@inheritdoc}
	 */
	public function is_valid( $value ) {

		$strict   = (bool) $this->options[ 'strict' ];
		$haystack = $this->options[ 'haystack' ];

		if ( ! in_array( $value, $haystack, $strict ) ) {
			$this->set_error_message( self::NOT_IN_ARRAY, $value );

			return FALSE;
		}

		return TRUE;
	}

}
