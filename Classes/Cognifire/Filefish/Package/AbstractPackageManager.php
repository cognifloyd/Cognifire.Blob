<?php
namespace Cognifire\Filefish\Package;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Cognifire.Filefish".    *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */


use TYPO3\Flow\Annotations as Flow;

/**
 *
 */
abstract class AbstractPackageManager implements PackageManagerInterface {

	/**
	 * The type of packages that this package manager supports
	 *
	 * @var string
	 * @api
	 */
	static protected $supportedPackageType = NULL;

	/**
	 * @throws Exception
	 * @return string the type of packages that this package manager supports.
	 * @api
	 */
	static public function getPackageManagerType() {
		if (!is_string(static::$supportedPackageType)) {
			throw new Exception('Supported package type in class ' . __CLASS__ . ' is empty.', 1377714653);
		}
		return static::$supportedPackageType;
	}
}