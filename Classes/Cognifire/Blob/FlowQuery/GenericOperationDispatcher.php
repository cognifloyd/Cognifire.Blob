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


use Cognifire\Blob\Exception;
use TYPO3\Eel\FlowQuery\FlowQuery;
use TYPO3\Eel\FlowQuery\Operations\AbstractOperation;
use TYPO3\Flow\Annotations as Flow;

/**
 *
 */
class GenericOperationDispatcher extends AbstractOperation {

	/**
	 * The short name of this operation
	 *
	 * @var string
	 */
	static protected $shortName = 'generic-operation-dispatcher';

	/**
	 * The current operation name
	 *
	 * @var  string
	 */
	protected $operationName;

	/**
	 * The class name of the class that can run this operation in the format:
	 *   Vendor\Namespace\Class
	 *
	 * @var  string
	 */
	protected $operationClassName;

	/**
	 * The method that this operation is mapped to
	 *
	 * @var  string
	 */
	protected $operationMethod;

	/**
	 * Whether or not this operation is final. This only applies to operations that must be dispatched,
	 * and takes precedence over $this::final which is "False"
	 *
	 * @var  boolean
	 */
	protected $operationIsFinal;

	/**
	 * The mediaType that this operation works with.
	 *
	 * @var  string
	 */
	protected $mediaType;

	/**
	 * {@inheritdoc}
	 *
	 * The normal OperationResolver should not use this operation dispatcher.
	 *
	 * @param array (or array-like object) $context onto which this operation should be applied
	 * @return boolean TRUE if the operation can be applied onto the $context, FALSE otherwise
	 * @api
	 */
	public function canEvaluate($context) {
		return FALSE;
	}

	/**
	 * @param $operationName string
	 * @param $operationMap  array
	 */
	public function __construct($operationName, array $operationMap) {
		$this->operationName = $operationName;
		$this->mediaType = $operationMap['mediaType'];
		$this->operationIsFinal = $operationMap['final'];
		$this->operationClassName = $operationMap['className'];
		if(!isset($operationMap['methodAlias'])) {
			$this->operationMethod = $operationName;
		} else {
			$this->operationMethod = $operationMap['methodAlias'];
		}
	}

	/**
	 * Evaluate the operation on the objects inside $flowQuery->getContext(),
	 * taking the $arguments into account.
	 *
	 * The resulting operation results should be stored using $flowQuery->setContext().
	 *
	 * If the operation is final, evaluate should directly return the operation result.
	 *
	 * @param FlowQuery $flowQuery the FlowQuery object
	 * @param array     $arguments the arguments for this operation
	 * @throws Exception
	 * @return mixed|null if the operation is final, the return value
	 */
	public function evaluate(FlowQuery $flowQuery, array $arguments) {
		/** @var EditorContextInterface $context */
		$context = $flowQuery->getContext();
		$contextMediaType = $context->getMediaType();
		if($this->mediaType !== $contextMediaType) {
			throw new Exception(sprintf('This operation only works with the %s mediaType, but %s was provided. The operationResolver should have prevented this error.', $this->mediaType, $contextMediaType), 1380812975);
		}

		/** @var mixed|NULL $evaluationResult This is a final result, or if not final, the context should be modified, and this will be NULL. */
		$evaluationResult = $context->evaluateOperation($this->operationName, $arguments, $this->operationMethod, $this->operationClassName);
		if($this->operationIsFinal) {
			return $evaluationResult;
		}

		//TODO[cognifloyd] How does the callback interact with the context?
		// Should I move this into the context? I can't depend on everything that needs to interact with the context
		// going through this GenericOperationDispatcher, so this should probably move into the context.
		// Maybe it doesn't make sense to require specifying the className. Instead, we just pass the operationName
		// to the context, and let it figure out which class it needs to run it on.

		$flowQuery->setContext($context);
		return $flowQuery;
	}
}