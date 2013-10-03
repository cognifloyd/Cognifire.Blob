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
 *
 */
class FlowQueryContext implements \ArrayAccess {

	protected $finder;
	protected $queryPath;//Does ths become a mediaType specific context? Is this where Blobs come in?

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