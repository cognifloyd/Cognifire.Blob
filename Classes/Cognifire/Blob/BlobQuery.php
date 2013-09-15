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
use Cognifire\Blob\Utility\Files; //use TYPO3\Flow\Utility\Files;
use Cognifire\Blob\Utility\MediaTypes; //use TYPO3\Flow\Utility\MediaTypes;
use Cognifire\Blob\Utility\RecursiveCallbackFilterIterator;
use Cognifire\BuilderFoundation\Exception;
use Symfony\Component\Finder\Finder;
use TYPO3\Eel\FlowQuery\FlowQuery;
use TYPO3\Flow\Annotations as Flow;

/**
 * BlobQuery is a FlowQuery factory. The returned FlowQuery instance will contain all of the Blobs
 * from the given package that match the glob or mediaType filters.
 *
 * Each BlobQuery instance can only work with blobs from a single package at a time.
 *
 * Symfony/Finder is the core of BlobQuery, but BlobQuery provides the context and semantics.
 */
class BlobQuery {

	/**
	 * The derivative that this BlobQuery works with
	 *
	 * @var Derivative
	 */
	protected $derivative;

	/**
	 * The key of the boilerplate
	 *
	 * @var  string
	 */
	protected $boilerplateKey;

	/**
	 * File that match this Media/Mime Type will be provided to the FlowQuery object
	 * This only supports one mediaType for now, but could be turned into an array to deal with more.
	 *
	 * @var string
	 */
	protected $mediaType = '';

	/**
	 * Files that are in any of these paths or path globs will be provided to the FlowQuery object
	 * These paths are relative to the derivative's root directory.
	 * Note that symlinks and dot files are ignored (.. and . references are not allowed).
	 *
	 * @var array
	 */
	protected $paths = array();

	/**
	 * Files that are in any of these paths or path globs will not be provided to the FlowQuery object.
	 * These paths are relative to the derivative's root directory.
	 * Note that symlinks and dot files are ignored (.. and . references are not allowed).
	 *
	 * @var array
	 */
	protected $notPaths = array();

	/**
	 * Files that should be included whether or not they match the other filters.
	 *
	 * @var array of pathname strings (path + filename) relative to derivativePath
	 */
	protected $withFiles = array();

	/**
	 * @param mixed|string|Derivative $derivative  The identifier for this derivative
	 * @//param string                  $mediaType   the FlowQuery object will only have derivativeBlobs of this mediaType
	 * @//param mixed|string|array      $paths       the FlowQuery object will only have derivativeBlobs from these paths
	 * @throws Exception
	 */
	public function __construct($derivative = '') {
		if (is_string($derivative)) {
			$this->derivative = new Derivative($derivative);
		} elseif (is_object($derivative) && ('Derivative' === get_class($derivative))) {
			$this->derivative = $derivative;
		} else {
			$type = gettype($derivative);
			if ('object' === $type) {
				$type .= ' of class ' . get_class($derivative);
			}
			throw new Exception('BlobQuery requires a string or a Derivative, but ' . $type . ' was received.', 1375743984);
		}
	}

//	public function initializeObject() {
//		$this->setupFinder();
//		//don't scan till needed
//		$this->newFinder();
//	}

	/**
	 * Retrieve only the files of this media type.
	 * This only allows for one mediaType at a time.
	 *
	 * @param string $mediaType
	 * @return BlobQuery The current BlobQuery instance
	 * @api
	 */
	public function ofMediaType($mediaType) {
		$this->mediaType = $mediaType;
		return $this;
	}

	/**
	 * Restrict blobs to files that are in this directory or set of directories.
	 *
	 * @see Finder->in() and Finder->path()
	 *
	 * @param string|array $dirs A directory path or an array of directories
	 * @return BlobQuery The current BlobQuery instance
	 * @api
	 */
	public function in($dirs) {
		$this->paths = array_merge($this->paths, (array) $dirs);
		return $this;
	}

	/**
	 * Restrict blobs to files that are not in this directory or set of directories.
	 * The given directories must be relative to the derivative root.
	 *
	 * @see Finder->exclude() and Finder->notPath()
	 *
	 * @param string|array $dirs A directory path or an array of directories
	 * @return BlobQuery The current BlobQuery instance
	 * @api
	 */
	public function exclude($dirs) {
		$this->notPaths = array_merge($this->notPaths, (array) $dirs);
		return $this;
	}

	/**
	 * Add these files whether or not they match the other filters
	 *
	 * @param string|array $files the Files that should be included
	 * @throws Exception
	 * @return BlobQuery The current BlobQuery instance
	 * @api
	 */
	public function with($files) {
		foreach ((array) $files as $file) {
			if(strpos($file,'..') !== FALSE) {
				throw new Exception('Referencing parent paths is not supported, but ".." was found in ' . $file, 1379164580);
			}
		}
		$this->withFiles = array_merge($this->withFiles, (array) $files);
		return $this;
	}

	/**
	 * This should return some metadata about what packages, files, etc that have been selected in this BlobQuery.
	 *
	 * @return array
	 */
	public function introspect() {
		$foundFiles = array();
		//example
		/** @var $file \SplFileInfo */
		foreach ($this->getFinder() as $file) {
			$foundFiles[] = $file->getPathname();
		}

		return array(
			"derivative"      => '' . $this->derivative, //Get the string representation.
			//"derivativePath"  => $this->derivative->getAbsolutePath(),
			//"mediaType"       => $this->mediaType,
			"foundFiles" => $foundFiles,
			//"paths"           => $this->paths,
			//"notPaths"        => $this->notPaths,
			"boilerplateKey"  => $this->boilerplateKey
		);
	}

	/**
	 * Creates a FlowQuery with the derivativeBlobs
	 *
	 * @return FlowQuery
	 */
//	public function getFlowQuery() {
//		return new FlowQuery($this->derivativeBlobs);
//	}

	/**
	 * This is a Finder Factory. It builds and returns a Symfony/Finder instance based on the
	 * BlobQuery restrictions.
	 *
	 * @return Finder
	 */
	public function getFinder() {
		$derivativePath = $this->derivative->getAbsolutePath();

		$finder = new Finder();
		$finder //setup the finder
			->files()
			->ignoreUnreadableDirs()
			->useBestAdapter()//php adapter does not support glob on path() or notPath()

			/* VCS files/folders are ignored by default, but could be disabled if needed */
			//->ignoreVCS(FALSE)

			/* Hidden Files are ignored by default, but we might want to include .htaccess or something.
			   We could just not ignore the hidden files here, or make people be very explicit about adding
			   a particular hidden file or folder: inHiddenDirectory() addHiddenFile() addHiddenFiles() */
			//->ignoreDotFiles(FALSE)

			/* Symlinks are disabled by default as a security precaution.
			   However, if someone really needs symlinks, we'd need to make this configurable here.
			   If we do enable symlinks, then we'll still have to make sure that the destination
			   is "allowed" somehow. For example, a link to root '/' would be abusive.  */
			//->followLinks()
		;

		//in() and with() haven't been called, so use the derivative root
		if (!$this->paths && !$this->withFiles) {
			$finder->in($derivativePath);
		}

		$suffixes = MediaTypes::getFilenameExtensionsFromMediaType($this->mediaType);
		/** @var string $suffix a file extension for this mediaType */
		foreach ($suffixes as $suffix) {
			$finder->name('*.' . $suffix); //Each call of name is like an "OR"
		}

		/** @var string $path a path relative to the derivativePath */
		foreach ($this->paths as $path) {
			//Finder's path() accepts regex but not glob, while in() accepts glob but not regex
			//Plus, we'd prefer to use in() wherever possible, because path() path is just a filter,
			//but using in() to provide multiple starting points means that the other files aren't
			//even opened.
			if ($this->stringLooksLikeRegex($path)) {
				$finder->path($path);
			} else {
				$finder->in(Files::concatenatePaths(array($derivativePath, $path)));
			}
		}

		/** @var string $notPath a path relative to the derivativePath */
		foreach ($this->notPaths as $notPath) {
			//WARNING: exclude() is greedy, but notPath() is more greedy.
			//exclude(): If any of the directories relative to derivative root match, they're excluded.
			//           Strings only; No regex; No Glob.
			//notPath(): If any of the directories in the absolute path match, they're excluded.
			//           Strings or Regex only; No Glob.
			if ($this->stringLooksLikeRegex($notPath)) {
				$finder->notPath($notPath);
			} else {
				$finder->exclude($notPath);
			}
		}

		/** @var string $pathname A path to a file including the filename, relative to derivativePath */
		foreach ($this->withFiles as $relativePathname) {
			$pathname = Files::concatenatePaths(array($derivativePath, $relativePathname));
			$finder->append((array) $pathname);
		}

		return $finder;
	}

	/**
	 * Check the given string to see if it looks like it is regex.
	 *
	 * @param string $string The string to check for regex-like qualities
	 * @return bool
	 */
	protected function stringLooksLikeRegex($string) {
		$start = substr($string, 0, 1);
		return !ctype_alnum($start) && $start === substr($string, -1);
	}
}