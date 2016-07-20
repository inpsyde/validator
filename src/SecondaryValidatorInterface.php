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
 * Interface SecondaryValidatorInterface
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package inpsyde-validator
 * @license http://opensource.org/licenses/MIT MIT
 */
interface SecondaryValidatorInterface extends ExtendedValidatorInterface {

	/**
	 * @param ExtendedValidatorInterface $validator
	 *
	 * @return SecondaryValidatorInterface
	 */
	public static function with_validator( ExtendedValidatorInterface $validator );

}