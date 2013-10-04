<?php
namespace Cognifire\Filefish\FlowQuery;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Cognifire.Filefish".    *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */


use TYPO3\Flow\Annotations as Flow;

/**
 * Developers should not implement this interface. Instead, extend the AbstractEditorContext.
 */
interface EditorContextInterface extends \Traversable {

	/**
	 * Returns the mediaType that this context contains.
	 *
	 * @return string
	 */
	static public function getMediaType();

	/**
	 * Evaluates the operation in this context.
	 */
	public function evaluateOperation($operationName, $arguments, $operationMethod, $operationClassName);

}