<?php
namespace Cognifire\Filefish\Utility;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Cognifire.Filefish".    *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * This class comes from:
 * http://php.net/manual/en/class.recursivecallbackfilteriterator.php#110974
 */
class RecursiveCallbackFilterIterator extends \RecursiveFilterIterator {

	/**
	 * @var  callback
	 */
	protected $callback;

	/**
	 * @param \RecursiveIterator $iterator
	 * @param                    $callback
	 */
	public function __construct(\RecursiveIterator $iterator, $callback){
		$this->callback = $callback;
		parent::__construct($iterator);
	}

	/**
	 * {inheritdoc}
	 *
	 * @return boolean true if the current element is acceptable, otherwise false.
	 */
	public function accept() {
		$callback = $this->callback;
		return $callback(parent::current(), parent::key(), parent::getInnerIterator());
	}

	/**
	 * {inheritdoc}
	 *
	 * @return RecursiveCallbackFilterIterator containing the inner iterator's children.
	 */
	public function getChildren() {
		return new self($this->getInnerIterator()->getChildren(), $this->callback);
	}

	//inherits hasChildren() from \RecursiveFilterIterator
}