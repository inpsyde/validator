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
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package inpsyde-validator
 */
trait MultiValidatorValidatorsTrait {

	/**
	 * @var ExtendedValidatorInterface[]
	 */
	private $validators = [ ];

	/**
	 * @param ExtendedValidatorInterface $validator
	 *
	 * @return MultiValidatorInterface
	 * @see MultiValidatorInterface::add_validator()
	 */
	public function add_validator( ExtendedValidatorInterface $validator ) {

		$this->validators[] = $validator;

		return $this;
	}

	/**
	 * @see \Countable::count()
	 */
	public function count() {

		return count( $this->validators );
	}
}