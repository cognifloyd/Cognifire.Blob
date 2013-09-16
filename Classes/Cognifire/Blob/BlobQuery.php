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

use Cognifire\Blob\Domain\Model\Boilerplate;
use Cognifire\Blob\Domain\Model\Derivative;
use Cognifire\Blob\Domain\Service\IntegratorService;
use Cognifire\Blob\Utility\Files; //use TYPO3\Flow\Utility\Files;
use Cognifire\Blob\Utility\MediaTypes; //use TYPO3\Flow\Utility\MediaTypes;
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
	 * @Flow\Inject
	 * @var  IntegratorService
	 */
	protected $integrator;

	/**
	 * The derivative that this BlobQuery works on
	 *
	 * @var Derivative
	 */
	protected $derivative;

	/**
	 * The boilerplate that this BlobQuery is currently using
	 * change using from()
	 *
	 * @var  Boilerplate|NULL
	 */
	protected $boilerplate = NULL;

	/**
	 * File that match this Media/Mime Type will be provided to the FlowQuery object
	 * This only supports one mediaType for now, but could be turned into an array to deal with more.
	 *
	 * @var array of strings
	 */
	protected $mediaType = array();

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
	 * @param mixed|string|Boilerplate $boilerplate  The identifier for the derivative (use from() to change)
	 * @throws Exception
	 */
	public function __construct($derivative, $boilerplate = '') {
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
		if($boilerplate !== '') {
			$this->from($boilerplate);
		}
	}

//	public function initializeObject() {
//		$this->setupFinder();
//		//don't scan till needed
//		$this->newFinder();
//	}

	/**
	 * Retrieve only the files of this media type.
	 *
	 * @param string|array $mediaType the mediaTypes that may be used.
	 * @return BlobQuery The current BlobQuery instance
	 * @api
	 */
	public function ofMediaType($mediaType) {
		$this->mediaType = (array) $mediaType;
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
	 * identifies the boilerplate that blobs can be integrated from
	 *
	 * @param mixed|string|Boilerplate $boilerplate  The identifier for the derivative (use from() to change)
	 * @throws Exception
	 * @return $this
	 */
	public function from($boilerplate) {
		if (is_string($boilerplate)) {
			$this->boilerplate = new Boilerplate($boilerplate);
		} elseif (is_object($boilerplate) && ('Boilerplate' === get_class($boilerplate))) {
			$this->boilerplate = $boilerplate;
		} else {
			$type = gettype($boilerplate);
			if ('object' === $type) {
				$type .= ' of class ' . get_class($boilerplate);
			}
			throw new Exception('BlobQuery requires a string packageKey or a Boilerplate object to identify the boilerplate package, but ' . $type . ' was received.', 1375743984);
		}
		return $this;
	}

	/**
	 * integrate this preset from the Boilerplate into the Derivative
	 *
	 * @param string $presetName the name of a preset in the Boilerplate
	 * @throws Exception
	 * @return BlobQuery The current BlobQuery instance
	 * @api
	 */
	public function integrate($presetName) {
		$this->isIntegrable();
		//add files that the preset copied to this BlobQuery's selected files
		return $this;
	}

	/**
	 * integrate these files from the Boilerplate into the Derivative
	 *
	 * If a single file is provided in a string, then that file will be copied from
	 * the boilerplate to the derivative. For example, the following will copy FooBar.html
	 * from the Boilerplate's Resources folder to the Derivative's Resources folder.
	 *   $files = 'Resources/FooBar.html'
	 *
	 * If an array of files is provided, and the keys are integers, then each file
	 * in the array will be copied from boilerplate to derivative.
	 *   $files = array( 'Resources/FooBar.html', 'Resources/Baz.html' )
	 *
	 * If, however, an array of files is provided that has strings for keys, then the string
	 * should be a file in the Boilerplate, and the value should be the derivative file's location.
	 *   $files = array( 'Resources/Classes/FooBar.php' => 'Classes/Vendor/Package/FooBar.php' )
	 *
	 * @param string|array $files a file, or an array of files, to be copied to the derivative.
	 * @throws Exception
	 * @return BlobQuery The current BlobQuery instance
	 * @api
	 */
	public function integrateFiles($files) {
		$this->isIntegrable();
		$this->integrator->copyFiles($this->boilerplate, $this->derivative, (array) $files);
		$this->with(array_values((array) $files));
		return $this;
	}

	/**
	 * Checks this BlobQuery to see if it is ready to integrate a (part of a) boilerplate into a derivative
	 *
	 * @throws Exception
	 * @return void
	 */
	protected function isIntegrable() {
		if(is_null($this->boilerplate)) {
			throw new Exception('You must first select a boilerplate package using from().', 1379216665);
		}
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
			"boilerplate"  => $this->boilerplate
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

		$suffixes = array();
		/** @var string $mediaType an IANA media type string */
		foreach ($this->mediaType as $mediaType) {
			$suffixes = array_merge($suffixes, MediaTypes::getFilenameExtensionsFromMediaType($mediaType));
		}

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