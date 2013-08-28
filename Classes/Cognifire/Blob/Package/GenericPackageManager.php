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


use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Object\ObjectManagerInterface;
use TYPO3\Flow\Reflection\ReflectionService;

/**
 * A GenericPackageManager that works with both Flow Packages and Irregular Packages.
 *
 * For Flow Packages, commands are just routed through Flow's PackageManager.
 * An Irregular Package is a package that is not a Flow Package but still has a particular structure.
 * For Example, an extension for TYPO3.CMS can be an Irregular Package.
 *
 * You should use this class instead of the PackageManagerInterface. This class will figure out which PackageManager
 * to use and pass the calls on to it.
 *
 * The logic of this class was inspired by FlowQuery's OperationResolver.
 *
 * @Flow\Scope("singleton")
 * @api
 */
class GenericPackageManager {

	/**
	 * @Flow\Inject
	 * @var ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @Flow\Inject
	 * @var ReflectionService
	 */
	protected $reflectionService;

	/**
	 * $packageManagerClasses = array(
	 *   'flow' => flowPackageManager,
	 *   'TYPO3CMS' => Irregular\Typo3CmsExtensionManager,
	 *   'Symfony' => A PackageManager that does symfony based packages but implements the PackageManagerInterface
	 * )
	 *
	 * @var  array<PackageManagerInterface>
	 */
	protected $packageManagerClasses = array();

	/**
	 * Initializes $this->packageManagerClasses
	 *
	 * @throws Exception
	 */
	public function initializeObject() {
		$packageManagerClassNames = $this->reflectionService->getAllImplementationClassNamesForInterface('Cognifire\Blob\Package\PackageManagerInterface');
		foreach ($packageManagerClassNames as $packageManagerClassName) {
			/** @var $packageManagerClassName PackageManagerInterface */
			$supportedPackageType = $packageManagerClassName::getPackageManagerType();
			if (isset($this->packageManagerClasses[$supportedPackageType])) {
				throw new Exception(sprintf('%s cannot be registered as the PackageManager for type %s because %s has already been registered for that type.', $packageManagerClassName, $supportedPackageType, $this->packageManagerClasses[$supportedPackageType]), 1377715263);
			} else {
				$this->packageManagerClasses[$supportedPackageType] = $packageManagerClassName;
			}
		}
	}

	/**
	 * Figures out what kind of package type a package is, and returns the appropriate package manager.
	 *
	 * This only returns the FlowPackageManager for now.
	 * TODO[cognifloyd] Figure out how to determine which package manager to return. Maybe use packageManager->isPackageKeyValid()
	 *
	 * @return PackageManagerInterface
	 */
	protected function resolvePackageManager() {
		return $this->objectManager->get($this->packageManagerClasses['Flow']);
	}

	/**
	 * Calls the method on the appropriate package manager.
	 *
	 * @param string $method
	 * @param array  $arguments
	 * @return mixed
	 */
	public function __call($method, array $arguments) {
		$packageManager = $this->resolvePackageManager();
		return call_user_func_array(array($packageManager,$method),$arguments);
	}

}