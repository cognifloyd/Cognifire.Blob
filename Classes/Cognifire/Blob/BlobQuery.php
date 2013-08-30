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
use Cognifire\Blob\Utility\MediaTypes;//use TYPO3\Flow\Utility\MediaTypes;
use Cognifire\Blob\Utility\RecursiveCallbackFilterIterator;
use TYPO3\Eel\FlowQuery\FlowQuery;
use TYPO3\Flow\Annotations as Flow;

/**
 * BlobQuery is a FlowQuery factory. The returned FlowQuery instance will contain all of the Blobs
 * from the given package that match the glob or mediaType filters.
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
	 * File that match this Media/Mime Type will be provided to the FlowQuery object
	 * This only supports one mediaType for now, but could be turned into an array to deal with more.
	 *
	 * @var string
	 */
	protected $mediaTypeFilter = '';

	/**
	 * File that match any of these filename filters will be provided to the FlowQuery object
	 *
	 * @var array
	 */
	protected $filenameFilters = array();

	/**
	 * File that match any of these path filters will be provided to the FlowQuery object
	 * These paths are relative to the derivative path root.
	 * Note that symlinks and dot files are ignored (.. and . references are not allowed).
	 *
	 * @var array
	 */
	protected $pathFilters = array();

	/**
	 * my awesome debugging variable
	 *
	 * @var array
	 */
	protected $lovebug = array();

	/**
	 * @param mixed|string|Derivative $derivative  The identifier for this derivative
	 * @param string                  $mediaType   the FlowQuery object will only have derivativeBlobs of this mediaType
	 * @param mixed|string|array      $paths       the FlowQuery object will only have derivativeBlobs from these paths
	 * @throws Exception
	 */
	public function __construct($derivative = '', $mediaType = '', $paths = array()) {
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
		$this->mediaTypeFilter = $mediaType;
		$this->addPathsFilter($paths);
	}

	public function initializeObject() {
		$this->scanForMatchingDerivativeBlobs();
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
	protected function scanForMatchingDerivativeBlobs() {
		$derivativePath = $this->derivative->getAbsolutePath();
		$derivativePathLength = strlen($derivativePath);

		$suffixes = MediaTypes::getFilenameExtensionsFromMediaType($this->mediaTypeFilter);
		$suffixLength = array();
		if(!$suffixes) {
			$includeMediaType = TRUE;//skips processing based on mediaType.
		} else {
			$includeMediaType = FALSE;
			foreach ($suffixes as $suffix) {
				$suffixLength[] = strlen($suffix);
			}
		}

		$includeThisHiddenFile = FALSE;
		$includeFilename       = TRUE;
		$excludeFilename       = FALSE;
		$includePath           = TRUE;
		$excludePath           = FALSE;

		/**
		 * If no filters are provided, everything gets included. If *any* filters are
		 * provided, then nothing is included except for files that match at least one
		 * of each type of filter (one mediaType filter, one filename filter, and one
		 * path filter).
		 *
		 * If a directory is excluded, nothing in it can be explicitly included,
		 * because when we return FALSE, we won't descend into that directory.
		 * In effect, that means that an exclusion is stronger than an inclusion.
		 *
		 * @param $fileInfo \SplFileInfo
		 * @return boolean true if the current element is acceptable, otherwise false.
		 */
		$filter = function($fileInfo) use ( $derivativePath, $derivativePathLength,
											$suffixes, $suffixLength,
											$includeMediaType, $includeThisHiddenFile,
											$includeFilename, $excludeFilename,
											$includePath, $excludePath ) {
			$filename = $fileInfo->getFilename();
			$path = $fileInfo->getPathname();
			$isDir = $fileInfo->isDir();


			//TODO[cognifloyd] Once we require PHP 5.4 (which provides $this in anonymous functions), I would like to break each section into a separate method.
			/* MediaType filtering */
				if ($isDir && $includeMediaType === FALSE) {
					//MediaType processing doesn't apply to directories
					$includeMediaType = TRUE;
				}

				//an alternate algorithm would compare the mediatype instead of the suffix.
				//$mediaType = MediaTypes::getMediaTypeFromFilename($filename);
				while ($includeMediaType === FALSE && list($i, $suffix) = each($suffixes)) {
					if (substr($filename, -$suffixLength[$i]) === $suffix) {
						$includeMediaType = TRUE;
					}
				}

			/* filename filtering */


			/* path filtering */


			if (   $includeMediaType                      //MediaType
				&& $includeFilename  && !$excludeFilename //FileName
				&& $includePath      && !$excludePath     //Path
				//Hidden files can be included explicitly, but we filter any other hidden files here:
				&& ($includeThisHiddenFile || $filename[0] !== '.') ) {
					return TRUE;
			}
			return FALSE;
		};

		$files = Files::readDirectoryRecursively(
			$derivativePath,
			NULL, //only supports a single suffix, but I want to check for more than one.
			TRUE, //return hidden files (beginning with dot) So that we can include some of them if desired.
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
			"derivative" => '' . $this->derivative, //Get the string representation.
			"derivativePath" => $this->derivative->getAbsolutePath(),
			"mediaType" => $this->mediaTypeFilter,
			"derivativeBlobs" => $this->derivativeBlobs,
			"pathFilters" => $this->pathFilters,
			"boilerplateKey" => $this->boilerplateKey,
			"lovebug" => $this->lovebug
		);
	}
}