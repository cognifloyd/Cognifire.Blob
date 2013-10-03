<?php
namespace Cognifire\Blob\FlowQuery;

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
 * This is a FlowQuery context through which builders can access/edit sets of files.
 *
 * Builder packages should provide one AbstractBuilderContext for each mediaType that the Builder understands.
 *
 * @api
 */
abstract class AbstractBuilderContext implements \ArrayAccess, BuilderContextInterface {

	/**
	 * The mediaType that this context can handle.
	 *
	 * @var  string
	 * @api
	 */
	static protected $mediaType = NULL;

	/**
	 * All available files of this mediaType
	 *
	 * This would be the Finder wrapper
	 *
	 * @var  mixed
	 */
	protected $packageFiles;

	/**
	 * Work with this file
	 *
	 * This would be the QueryPath wrapper
	 *
	 * @var  mixed
	 */
	protected $currentFile;

	/**
	 *
	 * @return string
	 */
	static public function getMediaType() {
		return static::$mediaType;
	}

	/**
	 * Whether a offset exists
	 *
	 * @param mixed $offset An offset to check for.
	 * @return boolean true on success or false on failure.
	 */
	public function offsetExists($offset) {

	}

	/**
	 * Offset to retrieve
	 * @param mixed $offset The offset to retrieve.
	 * @return mixed Can return all value types.
	 */
	public function offsetGet($offset) {

	}

	/**
	 * Offset to set
	 * @param mixed $offset The offset to assign the value to.
	 * @param mixed $value  The value to set.
	 * @return void
	 */
	public function offsetSet($offset, $value) {

	}

	/**
	 * Offset to unset
	 * @param mixed $offset The offset to unset.
	 * @return void
	 */
	public function offsetUnset($offset) {

	}
}