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
use Cognifire\Blob\Exception;

/**
 * The basic Boilerplate
 *
 * TODO[cognifloyd] If an object with the boilerplateKey already exists it should be returned instead of a new one. How do I do that?
 * @api
 */
class Boilerplate {

	/**
	 * @Flow\Inject
	 * @var PackageManagerInterface
	 */
	protected $packageManager;

	/**
	 * The identifier for this boilerplate, typically a packageKey.
	 *
	 * @var string
	 */
	protected $boilerplateKey;

	/**
	 * Path to the directory where this boilerplate is stored.
	 *
	 * @var string
	 */
	protected $absolutePath = '';

	/**
	 * an collection of the Presets that are available in this boilerplate.
	 * key = name of preset (should be unique per boilerplate package)
	 * value = a Preset object.
	 * @var  array<PresetInterface>
	 */
	protected $presets = array();

	/**
	 * Boilerplate constructor
	 *
	 * The boilerplateKey should be a valid Flow package key.
	 *
	 *     $b = new Boilerplate('Vendor.PackageName');
	 *
	 * @param string                  $boilerplateKey  The identifier for this derivative
	 * @throws Exception
	 */
	public function __construct($boilerplateKey) {
		$this->boilerplateKey = $boilerplateKey;
	}

	/**
	 * Initializer for initialization that needs injected properties like the PackageManager.
	 *
	 * @throws Exception
	 */
	public function initializeObject() {
		if(!$this->packageManager->isPackageKeyValid($this->boilerplateKey)) {
			throw new Exception('Package key ' . $this->boilerplateKey . ' is not valid. Only UpperCamelCase with alphanumeric characters in the format <VendorName>.<PackageKey>, please!', 1379215736);
		}
		if(!$this->packageManager->isPackageAvailable($this->boilerplateKey)) {
			throw new Exception('The boilerplate package, ' . $this->boilerplateKey . ', is not available!', 1379215659);
		}
		$this->absolutePath = $this->packageManager->getPackage($this->boilerplateKey)->getPackagePath();
		if( $this->absolutePath === '' ) {
			throw new Exception('Something died, because the absolutePath of the package looks like roadkill.', 1379215676);
		}
	}

	/**
	 * Return the absolutePath to where files of this Derivative is stored.
	 *
	 * @api
	 * @return string
	 */
	public function getAbsolutePath() {
		return $this->absolutePath;
	}

	/**
	 *
	 * @param string $presetName
	 * @return PresetInterface
	 */
	public function getPreset($presetName) {
		return $this->presets[$presetName];
	}

	/**
	 * This will return the boilerplateKey as the only string representation that makes sense for a Derivative.
	 *
	 * @api
	 * @return string
	 */
	public function __toString() {
		return $this->boilerplateKey;
	}

}