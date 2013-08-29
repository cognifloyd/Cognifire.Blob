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
use Cognifire\Blob\Utility\Files;//use TYPO3\Flow\Utility\Files;
use Cognifire\Blob\Utility\RecursiveCallbackFilterIterator;
use TYPO3\Eel\FlowQuery\FlowQuery;
use TYPO3\Flow\Annotations as Flow;

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
	protected $pathFilters = array();

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
		$this->pathFilters = array_merge($this->pathFilters, $pathFilter);
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
		$derivativePathLength = strlen($derivativePath);

		/**
		 *
		 * @param $fileInfo \SplFileInfo
		 * @param $pathname string
		 * @param $iterator RecursiveCallbackFilterIterator
		 * @param $filename string
		 * @return boolean true if the current element is acceptable, otherwise false.
		 */
		$filter = function($fileInfo, $pathname, $iterator, $filename) use ($derivativePath, $derivativePathLength) {
			//Hidden files can be included explicitly, but we filter any other hidden files here.
			if ($filename[0] === '.') {
				return FALSE;
			}
			return TRUE;
		};

		$files = Files::readDirectoryRecursively(
			$derivativePath,
			NULL, //suffix
			TRUE, //return hidden files (beginning with dot)
			FALSE, //don't return real path (dest of symlinks)
			FALSE, //follow symlinks is disabled so that no one can link to / or something.
			$filter
		); //returns an single dimensional array of all filenames w/ absolute paths.
		$this->derivativeBlobs = $files;

		/*foreach ($files as $pathAndFileName => $file) {
			$relativePath = substr($pathAndFileName, $derivativePathLength);
			if($this->pathMatchesFilters($relativePath)) {
				$this->derivativeBlobs[$relativePath] = $pathAndFileName;
			}
		}*/
	}

	/**
	 *
	 * @param $relativePath string Path relative to $this->derivativePath
	 * @return boolean
	 */
	protected function pathMatchesFilters($relativePath) {
		$derivativePath = $this->derivative->getAbsolutePath();

		/*foreach ($this->pathFilters as $pathFilter) {
			//compare each pathGlob with the relativePath. return FALSE on first not matching result.
		}
		//check the file at relative path against $this->filterType and return FALSE if it doesn't match.
		*/
		return TRUE;
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
			"pathFilters" => $this->pathFilters,
			"typeFilter" => $this->typeFilter
		);
	}
}