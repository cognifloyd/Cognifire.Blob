<?php
namespace Cognifire\Blob\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Cognifire.Blob".        *
 *                                                                        *
 *                                                                        */

use Cognifire\Blob\BlobQuery;
use Cognifire\Blob\Derivative;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\CommandController;

/**
 * @Flow\Scope("singleton")
 */
class BlobCommandController extends CommandController {

	/**
	 * An example command
	 *
	 * The comment of this command method is also used for TYPO3 Flow's help screens. The first line should give a very short
	 * summary about what the command does. Then, after an empty line, you should explain in more detail what the command
	 * does. You might also give some usage example.
	 *
	 * It is important to document the parameters with param tags, because that information will also appear in the help
	 * screen.
	 *
	 * @return void
	 */
	public function exampleCommand() {
		$this->outputLine('"Out damned spot!" --Lady MacBeth');
	}

	/**
	 * copy file test
	 *
	 * this should copy a file.
	 *
	 * @return void
	 */
	public function copyTestCommand() {
		$d = new Derivative('Cognifire.EmptyBoilerplate','Configuration/','text/plain');
		$this->outputLine("key: " . $d);
		$this->outputLine(print_r($d->introspect(), TRUE));
		//$d = new Derivative();
		//$this->outputLine("key: " . $d);
	}

	public function newDerivativeCommand() {
		$d = new Derivative('Cognifire.EmptyBoilerplate','Configuration/','text/plain');
		$this->outputLine("with key: " . $d);
		$this->outputLine($d->getAbsolutePath());

		/*$d = new Derivative();
		$this->outputLine("without key: " . $d);
		$this->outputLine($d->getAbsolutePath());
		$this->outputLine(print_r(scandir(FLOW_PATH_DATA . '/Blob'), TRUE));
		*/
	}
}

?>