<?php
namespace Cognifire\Blob\Domain\Service;

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
use Cognifire\Blob\Exception;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Utility\Files;

/**
 * This service does the heavy lifting of copying things from a boilerplate to a derivative.
 *
 * These services are not inherently part of a Boilerplate or a Derivative.
 * The boilerplate may contain information about how to copy and modify sets of files (this is
 * called a preset), but it does not care where it gets copied or how it gets copied. The derivative
 * cares about which files came from which Boilerplates, and how they have been modified, but
 * it does not care how those files get copied and modified.
 *
 * If any logic can be clearly assigned to a Boilerplate or a Derivative, then it should be there.
 * Otherwise, it should be here.
 *
 * @Flow\Scope("singleton")
 */
class IntegratorService {

	/**
	 * This copies the indicated files or directories from Boilerplate to Derivative.
	 *
	 * If numeric keys are provided, then boilerplate and derivative locations are the same.
	 *   $files = array( 'Resources/FooBar.html' )
	 *
	 * If string keys are provided, then the key is the boilerplate location, and the
	 * value is the derivative location.
	 *   $files = array( 'Resources/Classes/FooBar.php' => 'Classes/Vendor/Package/FooBar.php' )
	 *
	 * @param $boilerplate Boilerplate Copy from this boilerplate package
	 * @param $derivative  Derivative  To this derivative package
	 * @param $files       array       An array of files to be copied
	 * @throws Exception
	 */
	public function copyFiles(Boilerplate $boilerplate, Derivative $derivative, array $files) {
		$boilerplatePath = $boilerplate->getAbsolutePath();
		$derivativePath = $derivative->getAbsolutePath();

		foreach ($files as $boilerplateFile => $derivativeFile) {
			if(!is_string($derivativeFile)) {
				throw new Exception('An array of strings (file locations) must be provided, but ' . gettype($derivativeFile) . ' was received.', 1379337925);
			}
			switch($type = gettype($boilerplateFile)) {
				case 'integer':
					$boilerplateFile = Files::concatenatePaths(array($boilerplatePath, $derivativeFile));
					break;
				case 'string':
					$boilerplateFile = Files::concatenatePaths(array($boilerplatePath, $boilerplateFile));
					break;
				default:
					throw new Exception('An array of strings (file locations) must be provided with either integer or string keys, but a key of type ' . $type . ' was received.', 1379337776);
			}
			$derivativeFile = Files::concatenatePaths(array($derivativePath, $derivativeFile));

			if(!file_exists($boilerplateFile)) {
				throw new Exception('The boilerplate file or directory, ' . $boilerplateFile . ' does not exist!', 1379338198);
			}

			if(is_dir($boilerplateFile)) {
				Files::copyDirectoryRecursively($boilerplateFile, $derivativeFile);
			} else {
				copy($boilerplateFile, $derivativeFile);
			}
		}
	}

	//TODO[cognifloyd] Figure out how presets get integrated.

}