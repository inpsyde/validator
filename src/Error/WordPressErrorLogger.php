<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the inpsyde-validator package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inpsyde\Validator\Error;

use Inpsyde\Validator\ExtendedValidatorInterface;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package inpsyde-validator
 */
class WordPressErrorLogger implements ErrorLoggerInterface {

	/**
	 * @var ErrorLoggerInterface
	 */
	private $logger;

	/**
	 * Constructor.
	 *
	 * @param string[] $messages An array of messages to replace default ones.
	 */
	public function __construct( array $messages = [ ] ) {

		$default = [
			self::INVALID_TYPE_NON_STRING      => __(
				'Invalid type given for <code>%value%</code>. String expected.',
				'inpsyde-validator'
			),
			self::INVALID_TYPE_NON_NUMERIC     => __(
				'Invalid type given for <code>%value%</code>. Integer or float expected.',
				'inpsyde-validator'
			),
			self::INVALID_TYPE_NON_SCALAR      => __(
				'Invalid type given for <code>%value%</code>. String, integer or float expected.',
				'inpsyde-validator'
			),
			self::INVALID_TYPE_NON_TRAVERSABLE => __(
				'Invalid type given for <code>%value%</code>. Array or object implementing Traversable expected.',
				'inpsyde-validator'
			),
			self::INVALID_TYPE_NON_DATE        => __(
				'Invalid type given for <code>%value%</code>. String, integer, array or DateTime expected.',
				'inpsyde-validator'
			),
			self::NOT_BETWEEN                  => __(
				'The input <code>%value%</code> is not between <code>%min%</code> and <code>%max%</code>, inclusively.',
				'inpsyde-validator'
			),
			self::NOT_BETWEEN_STRICT           => __(
				'The input <code>%value%</code> is not strictly between <code>%min%</code> and <code>%max%</code>.',
				'inpsyde-validator'
			),
			self::INVALID_DATE                 => __(
				'The input <code>%value%</code> does not appear to be a valid date.',
				'inpsyde-validator'
			),
			self::INVALID_DATE_FORMAT          => __(
				'The input <code>%value%</code> does not fit the date format <code>%format%</code>.',
				'inpsyde-validator'
			),
			self::NOT_GREATER                  => __(
				'The input <code>%value%</code> is not greater than <code>%min%</code>.',
				'inpsyde-validator'
			),
			self::NOT_GREATER_INCLUSIVE        => __(
				'The input <code>%value%</code> is not greater or equal than <code>%min%</code>.',
				'inpsyde-validator'
			),
			self::NOT_IN_ARRAY                 => __(
				'The input <code>%value%</code> is not in the haystack: <code>%haystack%</code>.',
				'inpsyde-validator'
			),
			self::NOT_LESS                     => __(
				'The input <code>%value%</code> is not less than <code>%max%</code>.',
				'inpsyde-validator'
			),
			self::NOT_LESS_INCLUSIVE           => __(
				'The input <code>%value%</code> is not less or equal than <code>%max%</code>.',
				'inpsyde-validator'
			),
			self::IS_EMPTY                     => __(
				'This value should not be empty.',
				'inpsyde-validator'
			),
			self::NOT_MATCH                    => __(
				'The input does not match against pattern <code>%pattern%</code>.',
				'inpsyde-validator'
			),
			self::REGEX_INTERNAL_ERROR         => __(
				'There was an internal error while using the pattern <code>%pattern%</code>.',
				'inpsyde-validator'
			),
			self::NOT_URL                      => __(
				'The input <code>%value%</code> is not a valid URL.',
				'inpsyde-validator'
			),
			self::INVALID_DNS                  => __(
				'The host for the given input <code>%value%</code> could not be resolved.',
				'inpsyde-validator'
			),
			self::MULTIPLE_ERRORS              => __(
				'The host for the given input <code>%value%</code> could not be resolved.',
				'inpsyde-validator'
			),
		];

		$this->logger = new ErrorLogger( array_merge( $default, array_filter( $messages, 'is_string' ) ) );
	}

	/**
	 * @inheritdoc
	 */
	public function getIterator() {

		return $this->logger->getIterator();
	}

	/**
	 * @inheritdoc
	 */
	public function log_error( ExtendedValidatorInterface $validator, $error_template = NULL ) {

		return $this->logger->log_error( $validator, $error_template );
	}

	/**
	 * @inheritdoc
	 */
	public function get_error_messages( $error_code = NULL ) {

		return $this->logger->log_error( $error_code );
	}

	/**
	 * @inheritdoc
	 */
	public function get_error_codes() {

		return $this->logger->get_error_codes();
	}

	/**
	 * @inheritdoc
	 */
	public function get_last_message( $error_code = NULL ) {

		return $this->logger->get_last_message( $error_code );
	}

	/**
	 * @inheritdoc
	 */
	public function use_error_template( $error_code, $error_template ) {

		return $this->logger->use_error_template( $error_code, $error_template );
	}

	/**
	 * @inheritdoc
	 */
	public function merge( ErrorLoggerInterface $logger ) {

		$this->logger = $this->logger->merge( $logger );

		return $this->logger;
	}

	/**
	 * @inheritdoc
	 */
	public function count() {

		return $this->logger->count();
	}
}