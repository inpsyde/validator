<?php
namespace Inpsyde\Validator;

/**
 * Class AbstractValidator
 *
 * @package Inpsyde\Validator
 */
abstract class AbstractValidator implements ValidatorInterface {

	/**
	 * The Message-Templates for Error-Description.
	 *
	 * @var     array
	 */
	protected $message_templates = [ ];

	/**
	 * Contains all error-Messages after Validation.
	 *
	 * @var     array
	 */
	protected $error_messages = [ ];

	/**
	 * Contains the Validation-Options.
	 *
	 * @var     array
	 */
	protected $options = [ ];

	/**
	 * @param array $options
	 * @param array $message_templates
	 *
	 * @return \Inpsyde\Validator\AbstractValidator
	 */
	public function __construct( array $options = [ ], array $message_templates = [ ] ) {


		foreach ( $options as $name => $value ) {
			$this->options[ $name ] = $value;
		}

		foreach ( $message_templates as $name => $value ) {
			$this->message_templates[ $name ] = (string) $value;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_error_messages() {

		return array_unique( $this->error_messages );
	}

	/**
	 * Returns the stored messages templates.
	 *
	 * @return array
	 */
	public function get_message_templates() {

		return $this->message_templates;
	}

	/**
	 * Returns the error message template or empty string by a given name.
	 *
	 * @param    String $name
	 *
	 * @return    String $template
	 */
	public function get_message_template( $name ) {

		if ( ! isset( $this->message_templates[ $name ] ) ) {
			return '';
		}

		return $this->message_templates[ $name ];
	}

	/**
	 * Returns the stored options.
	 *
	 * @return array
	 */
	public function get_options() {

		return $this->options;
	}

	/**
	 *
	 * @param string $message_name
	 * @param mixed  $value
	 *
	 * @return ValidatorInterface
	 */
	protected function set_error_message( $message_name, $value ) {

		$this->error_messages[] = $this->create_error_message( $message_name, $value );

		return $this;
	}

	/**
	 * Creating an Error-Message for the given messageName from an messageTemplate.
	 *
	 * @param   String $message_name
	 * @param   String $value
	 *
	 * @return  Null|String
	 */
	protected function create_error_message( $message_name, $value ) {

		$message = $this->get_message_template( $message_name );

		if ( ! is_scalar( $value ) ) {
			$value = $this->get_type_as_string( $value );
		}

		// replacing the placeholder for the %value%
		$message = str_replace( '%value%', $value, $message );

		// replacing the possible options-placeholder on the message
		foreach ( $this->options as $search => $replace ) {
			if ( is_array( $replace ) ) {
				$replace = var_export( $replace, TRUE );
			}
			$message = str_replace( '%' . $search . '%', $replace, $message );
		}

		return $message;
	}

	/**
	 * Converts non-scalar values into a readable string.
	 *
	 * @param   mixed $value
	 *
	 * @return  string $type
	 */
	protected function get_type_as_string( $value ) {

		return ( is_object( $value ) ? get_class( $value ) : gettype( $value ) );
	}

}