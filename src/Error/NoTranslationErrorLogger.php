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
class NoTranslationErrorLogger implements ErrorLoggerInterface {

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
			self::INVALID_TYPE_NON_STRING      => 'Invalid type given for <code>%value%</code>. String expected.',
			self::INVALID_TYPE_NON_NUMERIC     => 'Invalid type given for <code>%value%</code>. Integer or float expected.',
			self::INVALID_TYPE_NON_SCALAR      => 'Invalid type given for <code>%value%</code>. String, integer or float expected.',
			self::INVALID_TYPE_NON_TRAVERSABLE => 'Invalid type given for <code>%value%</code>. Array or object implementing Traversable expected.',
			self::INVALID_TYPE_NON_DATE        => 'Invalid type given for <code>%value%</code>. String, integer, array or DateTime expected.',
			self::NOT_BETWEEN                  => 'The input <code>%value%</code> is not between <code>%min%</code> and <code>%max%</code>, inclusively.',
			self::NOT_BETWEEN_STRICT           => 'The input <code>%value%</code> is not strictly between <code>%min%</code> and <code>%max%</code>.',
			self::INVALID_DATE                 => 'The input <code>%value%</code> does not appear to be a valid date.',
			self::INVALID_DATE_FORMAT          => 'The input <code>%value%</code> does not fit the date format <code>%format%</code>.',
			self::NOT_GREATER                  => 'The input <code>%value%</code> is not greater than <code>%min%</code>.',
			self::NOT_GREATER_INCLUSIVE        => 'The input <code>%value%</code> is not greater or equal than <code>%min%</code>.',
			self::NOT_IN_ARRAY                 => 'The input <code>%value%</code> is not in the haystack: <code>%haystack%</code>.',
			self::NOT_LESS                     => 'The input <code>%value%</code> is not less than <code>%max%</code>.',
			self::NOT_LESS_INCLUSIVE           => 'The input <code>%value%</code> is not less or equal than <code>%max%</code>.',
			self::IS_EMPTY                     => 'This value should not be empty.',
			self::NOT_MATCH                    => 'The input does not match against pattern <code>%pattern%</code>.',
			self::REGEX_INTERNAL_ERROR         => 'There was an internal error while using the pattern <code>%pattern%</code>.',
			self::NOT_URL                      => 'The input <code>%value%</code> is not a valid URL.',
			self::INVALID_DNS                  => 'The host for the given input <code>%value%</code> could not be resolved.',
			self::MULTIPLE_ERRORS              => 'The host for the given input <code>%value%</code> could not be resolved.',
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