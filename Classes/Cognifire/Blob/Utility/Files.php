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
	 * @param string  $path           Path to the directory which shall be read
	 * @param string  $suffix         If specified, only filenames with this extension are returned (eg. ".php" or "foo.bar")
	 * @param boolean $returnRealPath If turned on, all paths are resolved by calling realpath()
	 * @param boolean $returnDotFiles If turned on, also files beginning with a dot will be returned
	 * @param boolean $followSymLinks If turned off, does not follow symlinks
	 * @param callable $userFilter
	 * @throws Exception
	 * @return array Filenames including full path
	 * @api
	 */
	static public function readDirectoryRecursively($path, $suffix = NULL, $returnRealPath = FALSE, $returnDotFiles = FALSE, $followSymLinks = TRUE, /*callable*/ $userFilter = NULL) {
		if (!is_dir($path)) {
			throw new Exception('"' . $path . '" is no directory.', 1207253462);
		}

		$userFilterIsCallable = is_callable($userFilter);
		$suffixLength = strlen($suffix);

		/**
		 * Anonymous function to filter the DirectoryIterator's results
		 *
		 * @param $fileInfo \SplFileInfo
		 * @param $pathname string
		 * @param $iterator RecursiveCallbackFilterIterator
		 * @return boolean true if the current element is acceptable, otherwise false.
		 */
		$filter = function ($fileInfo, $pathname, $iterator) use ($suffix, $suffixLength, $returnDotFiles, $userFilter, $userFilterIsCallable) {

			$filename = $fileInfo->getFilename();
			if ($returnDotFiles === FALSE && $filename[0] === '.') {
				return FALSE;
			}
			if (($userFilterIsCallable && $userFilter($fileInfo, $pathname, $iterator, $filename)) || TRUE ) {
				if (($fileInfo->isFile() && ($suffix === NULL || substr($filename, -$suffixLength) === $suffix))
					|| $iterator->hasChildren()) {
					return TRUE;
				}
			}
			return FALSE;
		};

		$directoryIterator = new \RecursiveIteratorIterator(
			//We don't require PHP 5.4 yet so we provide this class.
			//new \RecursiveCallbackFilterIterator(
			new RecursiveCallbackFilterIterator(
				new \RecursiveDirectoryIterator(
					$path,
					//defaults: \FilesystemIterator::KEY_AS_PATHNAME|\FilesystemIterator::CURRENT_AS_FILEINFO
					$followSymLinks
						? \FilesystemIterator::UNIX_PATHS|\FilesystemIterator::SKIP_DOTS|\FilesystemIterator::FOLLOW_SYMLINKS
						: \FilesystemIterator::UNIX_PATHS|\FilesystemIterator::SKIP_DOTS
				), $filter
			)
		);

		$filenames = array();
		foreach ($directoryIterator as $pathname => $fileInfo) {
			$filenames[] = self::getUnixStylePath(($returnRealPath === TRUE ? realpath($pathname) : $pathname));
		}
		return $filenames;
	}
	// This version just switches to using FilesystemIterator instead of DirectoryIterator. It does not add the userFilter.
	/*static public function readDirectoryRecursively($path, $suffix = NULL, $returnRealPath = FALSE, $returnDotFiles = FALSE, &$filenames = array()) {
		if (!is_dir($path)) {
			throw new Exception('"' . $path . '" is no directory.', 1207253462);
		}

		//by default FilesystemIterator has KEY_AS_PATHNAME|CURRENT_AS_FILEINFO|SKIP_DOTS
		$directoryIterator = new \FilesystemIterator($path,\FilesystemIterator::FOLLOW_SYMLINKS);
		$suffixLength = strlen($suffix);

		foreach ($directoryIterator as $pathname => $fileInfo) {
			$filename = $fileInfo->getFilename();
			if ($returnDotFiles === FALSE && $filename[0] === '.') {
				continue;
			}
			if ($fileInfo->isFile() && ($suffix === NULL || substr($filename, -$suffixLength) === $suffix)) {
				$filenames[] = self::getUnixStylePath(($returnRealPath === TRUE ? realpath($pathname) : $pathname));
			}
			if ($fileInfo->isDir()) {
				self::readDirectoryRecursively($pathname, $suffix, $returnRealPath, $returnDotFiles, $filenames);
			}
		}
		return $filenames;
	}*/
}