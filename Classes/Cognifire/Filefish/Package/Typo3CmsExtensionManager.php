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
 * An IrregularPackageManager for TYPO3 CMS Extensions
 *
 * @Flow\Scope("singleton")
 */
class Typo3CmsExtensionManager extends AbstractPackageManager {

	/**
	 * {@inheritdoc}
	 *
	 * @var string
	 */
	static protected $supportedPackageType = 'TYPO3CMS';

	public function notImplementedYet() {
		throw new Exception('TYPO3 CMS Extension support is not implemented yet', 1377714343);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param string $packageKey The package key to validate
	 * @return boolean TRUE if the package key is valid, otherwise FALSE
	 * @api
	 */
	public function isPackageKeyValid($packageKey) {
		$this->notImplementedYet();
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param string $packageKey The key of the package to check
	 * @return boolean TRUE if the package is available, otherwise FALSE
	 * @api
	 */
	public function isPackageAvailable($packageKey) {
		$this->notImplementedYet();
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param string $packageKey The package key to use for the new package
	 * @return \TYPO3\Flow\Package\Package The newly created package
	 * @api
	 */
	public function createPackage($packageKey) {
		$this->notImplementedYet();
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param  string $packageKey The key of the package
	 * @return string             Path to the given package's main directory
	 * @api
	 */
	public function getPackagePath($packageKey) {
		$this->notImplementedYet();
	}
}