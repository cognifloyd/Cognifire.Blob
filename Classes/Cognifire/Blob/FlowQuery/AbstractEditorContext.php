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
 * Builder packages should provide one AbstractEditorContext for each mediaType that the Builder understands.
 *
 * @api
 */
abstract class AbstractEditorContext implements EditorContextInterface {

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
	 *
	 */
	public function evaluateOperation($operationName, $arguments, $operationMethod, $operationClassName) {
		//This will actually be called on querypath or whatever is used to edit files.
		$callback = array($operationClassName, $operationMethod);
		call_user_func_array($callback, $arguments);
	}

}