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
 * The contract for all PackageManagers. You should extend AbstractPackageManager instead of implementing this.
 */
interface PackageManagerInterface {

	/**
	 * @return string the type of packages that this package manager supports.
	 */
	static public function getPackageManagerType();

	/**
	 * Check the conformance of the given package key
	 *
	 * @param string $packageKey The package key to validate
	 * @return boolean TRUE if the package key is valid, otherwise FALSE
	 * @api
	 */
	public function isPackageKeyValid($packageKey);

	/**
	 * Returns TRUE if a package is available (the package's files exist in the packages directory)
	 * or FALSE if it's not. If a package is available it doesn't mean necessarily that it's active!
	 *
	 * @param string $packageKey The key of the package to check
	 * @return boolean TRUE if the package is available, otherwise FALSE
	 * @api
	 */
	public function isPackageAvailable($packageKey);

	/**
	 * Create a new package, given the package key
	 *
	 * TODO[cognifloyd] This shouldn't return a Flow Package...
	 *
	 * @param string $packageKey The package key to use for the new package
	 * @return \TYPO3\Flow\Package\Package The newly created package
	 * @api
	 */
	public function createPackage($packageKey);

	/**
	 * Returns the full path to a package, given its packageKey
	 *
	 * @param  string $packageKey The key of the package
	 * @return string             Path to the given package's main directory
	 * @api
	 */
	public function getPackagePath($packageKey);
}