<?php
namespace Cognifire\Blob\Utility;

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

/**
 * This changes the logic in readDirectoryRecursively to allow an anonymous filter function to be passed in.
 */
class Files extends \TYPO3\Flow\Utility\Files {

	/**
	 * Returns all filenames from the specified directory. Filters hidden files and
	 * directories.
	 *
	 * @param string   $path           Path to the directory which shall be read
	 * @param string   $suffix         If specified, only filenames with this extension are returned (eg. ".php" or "foo.bar")
	 * @param boolean  $returnRealPath If turned on, all paths are resolved by calling realpath()
	 * @param boolean  $returnDotFiles If turned on, also files beginning with a dot will be returned
	 * @param boolean  $followSymLinks If turned off, does not follow symlinks
	 * @param callable $userFilter     A callable function that takes one parameter the FileInfo object of a file to test.
	 * @ (removed) param array    $filenames      Internally used for the recursion - don't specify!
	 * @throws Exception
	 * @return array Filenames including full path
	 * @api
	 */
	//This version uses SplRecursiveCallableFilterIterator
	static public function readDirectoryRecursively($path, $suffix = NULL, $returnRealPath = FALSE, $returnDotFiles = FALSE, $followSymLinks = TRUE, /*callable*/ $userFilter = NULL) {
		if (!is_dir($path)) {
			throw new Exception('"' . $path . '" is no directory.', 1207253462);
		}

		$suffixLength = strlen($suffix);

		$userFilterIsCallable = is_callable($userFilter);
		/**
		 * Anonymous function to filter the DirectoryIterator's results
		 *
		 * @param $fileInfo \SplFileInfo Current file's FileInfo
		 * @param $pathname string       Current file's path
		 * @param $iterator RecursiveCallbackFilterIterator
		 * @return boolean true if the current element is acceptable, otherwise false.
		 */
		$filter = function ($fileInfo, $pathname, $iterator) use ($returnDotFiles, $suffix, $suffixLength, $userFilter, $userFilterIsCallable) {
			$filename = $fileInfo->getFilename();
			if ($returnDotFiles === FALSE && $filename[0] === '.') {
				return FALSE;
			}
			if (($fileInfo->isFile() && ($suffix === NULL || substr($filename, -$suffixLength) === $suffix)) || $iterator->hasChildren()) {
				if($userFilterIsCallable && !$userFilter($fileInfo)) {
					return FALSE;
				}
				return TRUE;
			}
			return FALSE;
		};

		$directoryIterator = new \RecursiveDirectoryIterator(
			$path,
			//defaults: \FilesystemIterator::KEY_AS_PATHNAME|\FilesystemIterator::CURRENT_AS_FILEINFO
			$followSymLinks
				? \FilesystemIterator::UNIX_PATHS|\FilesystemIterator::SKIP_DOTS|\FilesystemIterator::FOLLOW_SYMLINKS
				: \FilesystemIterator::UNIX_PATHS|\FilesystemIterator::SKIP_DOTS
		);
		if(version_compare(PHP_VERSION, '5.4.0', '>=')) {
			$filteredDirectory = new \RecursiveCallbackFilterIterator($directoryIterator,$filter);
		} else {
			//We don't require PHP 5.4 yet so we provide this class.
			$filteredDirectory = new RecursiveCallbackFilterIterator($directoryIterator, $filter);
		}

		$filenames = array();
		foreach (new \RecursiveIteratorIterator($filteredDirectory) as $pathname => $fileInfo) {
			$filenames[] = self::getUnixStylePath(($returnRealPath === TRUE ? realpath($pathname) : $pathname));
		}
		return $filenames;
	}
	// This version just switches to using FilesystemIterator instead of DirectoryIterator.
//	static public function readDirectoryRecursively($path, $suffix = NULL, $returnRealPath = FALSE, $returnDotFiles = FALSE, $followSymLinks = TRUE, /*callable*/ $userFilter = NULL, &$filenames = array()) {
//		if (!is_dir($path)) {
//			throw new Exception('"' . $path . '" is no directory.', 1207253462);
//		}
//
//		//by default FilesystemIterator has KEY_AS_PATHNAME|CURRENT_AS_FILEINFO|SKIP_DOTS
//		$directoryIterator = new \FilesystemIterator($path,$followSymLinks?\FilesystemIterator::FOLLOW_SYMLINKS:NULL);
//		$suffixLength = strlen($suffix);
//		$userFilterIsCallable = is_callable($userFilter);
//
//		foreach ($directoryIterator as $pathname => $fileInfo) {
//			/** @var $fileInfo \SplFileInfo */
//			$filename = $fileInfo->getFilename();
//			if ($returnDotFiles === FALSE && $filename[0] === '.') {
//				continue;
//			}
//			if ($fileInfo->isFile() && ($suffix === NULL || substr($filename, -$suffixLength) === $suffix)) {
//				//Which way is more performant? Calculating the pathname and filename in the userFilter or passing it in?
//				//if($userFilterIsCallable && !$userFilter($fileInfo, $pathname, $filename, $returnDotFiles, $suffix)) {
//				if($userFilterIsCallable && !$userFilter($fileInfo)) {
//					continue;
//				}
//				$filenames[] = self::getUnixStylePath(($returnRealPath === TRUE ? realpath($pathname) : $pathname));
//			}
//			if ($fileInfo->isDir()) {
//				self::readDirectoryRecursively($pathname, $suffix, $returnRealPath, $returnDotFiles, $followSymLinks, $userFilter, $filenames);
//			}
//		}
//		return $filenames;
//	}
}