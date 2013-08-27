<?php
namespace Cognifire\Blob;

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
use TYPO3\Flow\Package\PackageManager;
use TYPO3\Flow\Utility\Algorithms;
use TYPO3\Flow\Utility\Files;

/**
 * The basic derivative. Most often, this is a package, however it can be a temporary folder as well.
 */
class Derivative {

	/**
	 * @Flow\Inject
	 * @var PackageManager
	 */
	protected $packageManager;

	/**
	 * @var string The identifier for this derivative, typically a packageKey.
	 */
	protected $derivativeKey;

	/**
	 * @var array The paths that will be provided to the FlowQuery object
	 */
	protected $pathFilter = array();

	/**
	 * @var array The file or mime types that will be provided to the FlowQuery object
	 */
	protected $typeFilter = array();

	/**
	 * @var string path to the directory where this derivative is stored.
	 */
	protected $absolutePath = '';

	/**
	 * @param string $derivativeKey The identifier for this derivative
	 * @param mixed|string|array  $paths the FlowQuery object will only have blobs from these paths
	 * @param string $type the FlowQuery object will only have blobs of this type
	 */
	public function __construct($derivativeKey = '', $paths = array(), $type = '') {
		if($derivativeKey === '') {
			$derivativeKey = '@' . Algorithms::generateUUID();
			$this->absolutePath = Files::concatenatePaths(array(FLOW_PATH_DATA, 'Blob', $derivativeKey));
			Files::createDirectoryRecursively($this->absolutePath);
		} else {
			$this->absolutePath = $this->packageManager->getPackage($derivativeKey)->getPackagePath();
		}
		$this->derivativeKey = $derivativeKey;
		if(is_string($paths)) {
			$paths = array($paths);
		}
		$this->addPathsFilter($paths);
		$this->addTypeFilter($type);
	}

	/**
	 * Adds the paths to the filtered paths
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
	 *
	 * @param $typeFilter string
	 */
	protected function addTypeFilter($typeFilter) {
		$this->typeFilter = array_merge($this->typeFilter, array($typeFilter));
	}

	/**
	 * @return string
	 */
	public function getAbsolutePath() {
		return $this->absolutePath;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->derivativeKey;
	}

	public function introspect() {
		return array(
			"pathFilter" => $this->pathFilter,
			"typeFilter" => $this->typeFilter
		);
	}
}