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
use TYPO3\Flow\Package\PackageManagerInterface;
use TYPO3\Flow\Utility\Algorithms;
use TYPO3\Flow\Utility\Files;
use Cognifire\Blob\Exception;

/**
 * The basic Derivative. Most often, this is a package, however it can be a temporary folder as well.
 */
class Derivative {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Package\PackageManagerInterface
	 */
	protected $packageManager;

	/**
	 * The identifier for this derivative, typically a packageKey.
	 *
	 * @var string
	 */
	protected $derivativeKey;

	/**
	 * The paths that will be provided to the FlowQuery object
	 *
	 * @var array
	 */
	protected $pathFilter = array();

	/**
	 * The file or mime types that will be provided to the FlowQuery object
	 *
	 * @var array
	 */
	protected $typeFilter = array();

	/**
	 * Path to the directory where this derivative is stored.
	 *
	 * @var string
	 */
	protected $absolutePath = '';

	/**
	 * Derivative constructor
	 *
	 * All parameters are optional. If no parameters are provided, then this derivative will work with a new temporary
	 * directory under FLOW_PATH_DATA/Data/Blob. By default, this will generate a new Uuid prefixed with '@' as the
	 * directory's name. You can also specify your own temporary directory name as long as it begins with '@'.
	 * If the derivativeKey does not begin with '@', it should be a valid Flow package key. The package will be created
	 * if it does not exist.
	 *
	 * @param string                  $derivativeKey  @api The identifier for this derivative
	 * @param mixed|string|array      $paths          @api the FlowQuery object will only have blobs from these paths
	 * @param string                  $type           @api the FlowQuery object will only have blobs of this type
	 * @throws Exception
	 */
	public function __construct($derivativeKey = '', $paths = array(), $type = '') {
		if($derivativeKey === '') {
			$derivativeKey = '@' . Algorithms::generateUUID();
		}
		$this->derivativeKey = $derivativeKey;
		if(is_string($paths)) {
			$paths = array($paths);
		}
		$this->addPathsFilter($paths);
		$this->addTypeFilter($type);
	}

	/**
	 * Initializer for initialization that needs injected properties like PackageManager.
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
			$this->absolutePath = $this->packageManager->getPackage($this->derivativeKey)->getPackagePath();
		}
		if( $this->absolutePath === '' ) {
			throw new Exception('Something died, because the absolutePath of the package looks like roadkill.', 1377643412);
		}
	}

	/**
	 * Adds the paths to the filtered paths.
	 *
	 * @param $paths array<path strings>
	 */
	protected function addPathsFilter(array $paths) {
		$pathFilter = array();
		foreach ($paths as $path) {
			$pathFilter[] = Files::getUnixStylePath($path);
		}
		$this->pathFilter = array_merge($this->pathFilter, $pathFilter);
	}

	/**
	 * Adds the given file or media type to the list of filtered types
	 *
	 * @param $typeFilter string
	 */
	protected function addTypeFilter($typeFilter) {
		$this->typeFilter = array_merge($this->typeFilter, array($typeFilter));
	}

	/**
	 * Return the absolutePath to where files of this Derivative is stored.
	 *
	 * @return string
	 */
	public function getAbsolutePath() {
		return $this->absolutePath;
	}

	/**
	 * This will return the derivativeKey as the only string representation that makes sense for a Derivative.
	 *
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
			"pathFilter" => $this->pathFilter,
			"typeFilter" => $this->typeFilter
		);
	}
}