<?php
namespace Cognifire\Blob\Domain\Model;

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
use TYPO3\Flow\Utility\Algorithms;
use TYPO3\Flow\Utility\Files;
use Cognifire\Blob\Exception;
use Cognifire\Blob\Package\GenericPackageManagerInterface;

/**
 * The basic Derivative. Most often, this is a package, however it can be a temporary folder as well.
 *
 * TODO[cognifloyd] If an object with the derivativeKey already exists it should be returned instead of a new one. How do I do that?
 * @api
 */
class Derivative {

	/**
	 * @Flow\Inject
	 * @var GenericPackageManagerInterface
	 */
	protected $packageManager;

	/**
	 * The identifier for this derivative, typically a packageKey.
	 *
	 * @var string
	 */
	protected $derivativeKey;

	/**
	 * Path to the directory where this derivative is stored.
	 *
	 * @var string
	 */
	protected $absolutePath = '';

	/**
	 * Derivative constructor
	 *
	 * The derivativeKey parameter is optional.
	 *
	 * - If no key is provided, then this derivative will work with a new temporary directory in
	 *   FLOW_PATH_DATA/Data/Blob. By default, this will generate a new Uuid prefixed with '@' as the directory's name.
	 *
	 *     $d = new Derivative();
	 *
	 * - You can also specify your own temporary directory name as long as it begins with '@'.
	 *
	 *     $d = new Derivative('@directory-name');
	 *
	 * - If the derivativeKey does not begin with '@', it should be a valid Flow package key.
	 *   The package will be created if it does not exist.
	 *
	 *     $d = new Derivative('Vendor.PackageName');
	 *
	 * @param string                  $derivativeKey  The identifier for this derivative
	 * @throws Exception
	 */
	public function __construct($derivativeKey = '') {
		if($derivativeKey === '') {
			$derivativeKey = '@' . Algorithms::generateUUID();
		}
		$this->derivativeKey = $derivativeKey;
	}

	/**
	 * Initializer for initialization that needs injected properties like GenericPackageManager.
	 */
	public function initializeObject() {
		$this->initializeAbsolutePathAndCreateDirectory();
	}

	/**
	 * This initializes $this->absolutePath by looking up the package or temporary directory.
	 * If the package doesn't exist yet, it gets created. If the derivative is not a package,
	 * then the directory is created.
	 *
	 * @throws Exception
	 */
	protected function initializeAbsolutePathAndCreateDirectory() {
		//'@' means files should be stored in a temporary directory instead of as a package
		if('@' === substr($this->derivativeKey, 0, 1)) {
			$this->absolutePath = Files::concatenatePaths(array(FLOW_PATH_DATA, 'Blob', $this->derivativeKey));
			Files::createDirectoryRecursively($this->absolutePath);
		} else {
			if(!$this->packageManager->isPackageKeyValid($this->derivativeKey)) {
				throw new Exception('Package key' . $this->derivativeKey . 'is not valid. Only UpperCamelCase with alphanumeric characters in the format <VendorName>.<PackageKey>, please!', 1377641680);
			}
			if(!$this->packageManager->isPackageAvailable($this->derivativeKey)) {
				$this->packageManager->createPackage($this->derivativeKey);
			}
			$this->absolutePath = $this->packageManager->getPackagePath($this->derivativeKey);
		}
		if( $this->absolutePath === '' ) {
			throw new Exception('Something died, because the absolutePath of the package looks like roadkill.', 1377643412);
		}
	}

	/**
	 * Return the absolutePath to where files of this Derivative is stored.
	 *
	 * @api
	 * @return string
	 */
	public function getAbsolutePath() {
		return $this->absolutePath;
	}

	/**
	 * This will return the derivativeKey as the only string representation that makes sense for a Derivative.
	 *
	 * @api
	 * @return string
	 */
	public function __toString() {
		return $this->derivativeKey;
	}

	/**
	 * Temporary method to assist in manually testing.
	 *
	 * @return array
	 */
	public function introspect() {
		return array(
		);
	}

}