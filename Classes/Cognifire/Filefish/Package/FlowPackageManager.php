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
use TYPO3\Flow\Package\PackageManagerInterface as FlowPackageManagerInterface;

/**
 * PackageManager that works with Flow Packages
 *
 * @Flow\Scope("singleton")
 */
class FlowPackageManager extends AbstractPackageManager {

	/**
	 * {@inheritdoc}
	 *
	 * @var string
	 */
	static protected $supportedPackageType = 'Flow';

	/**
	 * @Flow\Inject
	 * @var FlowPackageManagerInterface
	 */
	protected $flowPackageManager;

	/**
	 * {@inheritdoc}
	 *
	 * @param string $packageKey The package key to validate
	 * @return boolean TRUE if the package key is valid, otherwise FALSE
	 */
	public function isPackageKeyValid($packageKey) {
		return $this->flowPackageManager->isPackageKeyValid($packageKey);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param string $packageKey The key of the package to check
	 * @return boolean TRUE if the package is available, otherwise FALSE
	 */
	public function isPackageAvailable($packageKey) {
		return $this->flowPackageManager->isPackageAvailable($packageKey);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param string $packageKey The package key to use for the new package
	 * @return \TYPO3\Flow\Package\Package The newly created package
	 */
	public function createPackage($packageKey) {
		return $this->flowPackageManager->createPackage($packageKey);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param  string $packageKey The key of the package
	 * @return string             Path to the given package's main directory
	 * @return string
	 */
	public function getPackagePath($packageKey) {
		return $this->flowPackageManager->getPackage($packageKey)->getPackagePath();
	}
}