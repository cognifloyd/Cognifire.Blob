<?php
namespace Cognifire\Blob\Package;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package                          *
 * "Cognifire.BuilderFoundation".                                         *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */


use Cognifire\Blob\Package\Irregular\IrregularPackageManagerInterface;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Package\PackageManagerInterface as FlowPackageManagerInterface;

/**
 * A GenericPackageManager that works with both Flow Packages and Irregular Packages.
 *
 * For Flow Packages, commands are just routed through Flow's PackageManager.
 * An Irregular Package is a package that is not a Flow Package but still has a particular structure.
 * For Example, an extension for TYPO3.CMS can be an Irregular Package.
 */
class GenericPackageManager implements GenericPackageManagerInterface {

	/**
	 * @Flow\Inject
	 * @var FlowPackageManagerInterface
	 */
	protected $flowPackageManager;

	/**
	 * An array of IrregularPackageManagers
	 *
	 * TODO[cognifloyd] Figure out how to populate this array: ('type-of-package' => IrregularPackageManager).
	 * Maybe I could do something like:
	 *   $packageManagers = array(
	 *     'flow' => flowPackageManager,
	 *     'TYPO3CMS' => Irregular\Typo3CmsExtensionManager,
	 *     'Symfony' => A PackageManager that does symfony based packages but implements the IrregularPackageManagerInterface
	 *   )
	 *
	 * @var  array<IrregularPackageManagerInterface>
	 */
	protected $irregularPackageManagers = array();

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