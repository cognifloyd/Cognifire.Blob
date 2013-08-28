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


use Cognifire\Blob\Domain\Model\Derivative;
use TYPO3\Eel\FlowQuery\FlowQuery;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\Files;

/**
 * BlobQuery is a FlowQuery factory. The returned FlowQuery instance will contain all of the Blobs
 * from the given package that match the glob or type filters.
 *
 * Each BlobQuery instance can only work with blobs from a single package at a time.
 */
class BlobQuery {

	/**
	 * The derivative that this BlobQuery works with
	 *
	 * @var Derivative
	 */
	protected $derivative;

	protected $boilerplateKey;

	protected $derivativeBlobs = array();
	protected $boilerplateBlobs = array();

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
	 * @param mixed|string|Derivative $derivative  The identifier for this derivative
	 * @param mixed|string|array      $paths          the FlowQuery object will only have derivativeBlobs from these paths
	 * @param string                  $type           the FlowQuery object will only have derivativeBlobs of this type
	 * @throws Exception
	 */
	public function __construct($derivative = '', $paths = array(), $type = '') {
		if(is_string($derivative)) {
			$this->derivative = new Derivative($derivative);
		} elseif(is_object($derivative) && ('Derivative' === get_class($derivative)) ) {
			$this->derivative = $derivative;
		} else {
			$type = gettype($derivative);
			if('object' === $type) {
				$type .= ' of class ' . get_class($derivative);
			}
			throw new Exception('BlobQuery requires a string or a Derivative, but '. $type . ' was received.', 1375743984);
		}
		if(is_string($paths)) {
			$paths = array($paths);
		}
		$this->addPathsFilter($paths);
		$this->addTypeFilter($type);
	}

	public function initializeObject() {
		$this->scanForDerivativeBlobs();
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
	 * Creates a FlowQuery with the derivativeBlobs
	 *
	 * @return FlowQuery
	 */
	public function getFlowQuery() {
		return new FlowQuery($this->derivativeBlobs);
	}

	/**
	 * Takes the filters into account and initializes $this->derivativeBlobs with available files to be represented as derivativeBlobs.
	 *
	 * This does not break each file into child derivativeBlobs, and it does not take into account derivativeBlobs that might span multiple
	 * files.
	 */
	protected function scanForDerivativeBlobs() {
		$derivativePath = $this->derivative->getAbsolutePath();

		// files and directories
		$files = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator(
				$derivativePath,
				\FilesystemIterator::UNIX_PATHS|\FilesystemIterator::SKIP_DOTS
			)/*,
			\RecursiveIteratorIterator::SELF_FIRST //returns directories as well.
			*/
			/*
			 * \RecursiveIteratorIterator::LEAVES_ONLY
			 * (on by default) only returns files which is acceptable because we expect packages to be
			 * stored in git which ignores empty folders anyway.
			 */
		);

		foreach ($files as $filename => $file) {
			$this->derivativeBlobs[] = $filename;
		}
	}

	/**
	 * This should return some metadata about what packages, files, etc that have been selected in this BlobQuery.
	 *
	 * @return array
	 */
	public function introspect() {
		return array(
			"boilerplateKey" => $this->boilerplateKey,
			"derivative" => '' . $this->derivative, //Get the string representation.
			"derivativePath" => $this->derivative->getAbsolutePath(),
			"derivativeBlobs" => $this->derivativeBlobs,
			"pathFilter" => $this->pathFilter,
			"typeFilter" => $this->typeFilter
		);
	}
}