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


use TYPO3\Eel\FlowQuery\OperationInterface;
use TYPO3\Eel\FlowQuery\OperationResolver as FlowQueryOperationResolver;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Object\ObjectManager;

/**
 * This operation resolver supports multiple operations in a single class.
 * Each operation must be registered, instead of relying only on the operation
 * classes that implement OperationInterface.
 */
class OperationResolver extends FlowQueryOperationResolver {

	/**
	 * a map of shortName (in FlowQuery), methodName (on EditorContext), and whether or
	 * it is a final operation.
	 * shortOperationName => operationMediaType => operationPriority =>
	 *     'final' => final
	 *     'className' => class that implements the method
	 *     'methodAlias' => methodAlias
	 *
	 * final, and class are required. methodAlias is optional. If it is not specified, the shortOperationName
	 * will be used as the methodName. This allows classes to have more descriptive method names while providing a
	 * more fluent interface in FlowQuery.
	 *
	 * @var array
	 */
	protected $operationMethodMap = array();

	/**
	 * Inject settings array
	 *
	 * @param  array $configuration
	 * @return void
	 */
	public function injectSettings(array $configuration) {
		$this->operationMethodMap = $configuration['operationMethodMap'];

		foreach ($this->operationMethodMap as $shortOperationName => $operationInfo) {
			foreach ($operationInfo as $mediaType => $priorities) {
				foreach ($priorities as $priority => $operationOptions) {
					$isFinalOperation = (isset($operationOptions['final']) && $operationOptions['final'] = TRUE);
					if ($isFinalOperation) {
						$this->finalOperationNames[$shortOperationName] = $shortOperationName;
						continue 3;
					}
				}
			}
		}
	}

	/**
	 * Resolve an operation, taking runtime constraints into account.
	 *
	 * @param string      $operationName
	 * @param array|mixed $context
	 * @return OperationInterface the resolved operation
	 */
	public function resolveOperation($operationName, $context) {

		if ($this->contextIsBuilderContext($context) //this must be checked first, as operationCanEvaluateOnContext depends on it
			&& $this->operationIsRegistered($operationName)
			&& $this->operationCanEvaluateOnContext($operationName, $context)) {

				$operationMediaType = $this->getContextMediaType($context);
				$highestPriority = max(array_keys($this->operationMethodMap[$operationName][$operationMediaType]));
				return $this->dispatchOperation($operationName, $operationMediaType, $highestPriority);
        }
        return parent::resolveOperation($operationName, $context);
	}

	/**
	 *
	 * @param string  $operationName
	 * @param string  $operationMediaType
	 * @param integer $priority
	 * @return GenericOperationDispatcher
	 */
	protected function dispatchOperation($operationName, $operationMediaType, $priority) {
		return new GenericOperationDispatcher($operationName, $operationMediaType, $this->operationMethodMap[$operationName][$operationMediaType][$priority]);
	}

	/**
	 * Checks to see if the operation is registered in the operationMethodMap
	 *
	 * @param string $operationName
	 * @return bool
	 */
	protected function operationIsRegistered($operationName) {
		if(isset($this->operationMethodMap[$operationName])) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 *
	 * @param string      $operationName operation to test
	 * @param array|mixed $context       the context this operation needs to work on
	 * @return boolean
	 */
	protected function operationCanEvaluateOnContext($operationName, $context) {
		$canEvaluate = FALSE;
		foreach ($this->operationMethodMap[$operationName] as $operationMediaType => $operationOptions) {
			$contextMediaType = $this->getContextMediaType($context);
			if($operationMediaType === $contextMediaType) {
				$canEvaluate = TRUE;
			}
		}
		return $canEvaluate;
	}

	/**
	 *
	 * @param EditorContextInterface $context
	 * @return string
	 */
	protected function getContextMediaType($context) {
		return $context->getMediaType();
	}

	/**
	 *
	 * @param array|mixed $context
	 * @return boolean
	 */
	protected function contextIsBuilderContext($context) {
		return ($context instanceof EditorContextInterface);
	}
}