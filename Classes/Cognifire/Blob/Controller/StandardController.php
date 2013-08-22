<?php
namespace Cognifire\Blob\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Cognifire.Blob".        *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

class StandardController extends \TYPO3\Flow\Mvc\Controller\ActionController {

	/**
	 * @return void
	 */
	public function indexAction() {
		$this->view->assign('foos', array(
			'bar', 'baz'
		));
	}

}

?>