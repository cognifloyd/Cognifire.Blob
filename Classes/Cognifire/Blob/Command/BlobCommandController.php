<?php
namespace Cognifire\Blob\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Cognifire.Blob".        *
 *                                                                        *
 *                                                                        */

use Cognifire\Blob\BlobQuery;
use Cognifire\Blob\Domain\Model\Derivative;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\CommandController;
use TYPO3\Flow\Utility\Files;

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
	public function notAnEasterEggCommand() {
		$this->outputLine('"Out damned spot!" --Lady MacBeth');
	}

	public function newDerivativeCommand() {
		$d = new Derivative();
		$this->outputLine("without key: " . $d);
		$this->outputLine($d->getAbsolutePath());
		$this->outputLine(print_r(scandir(FLOW_PATH_DATA . '/Blob'), TRUE));
		Files::removeDirectoryRecursively($d->getAbsolutePath());

		$d = new Derivative('@humbug');
		$dir = FLOW_PATH_DATA . '/Blob/@humbug';
		Files::createDirectoryRecursively($dir);
		$this->outputLine("with temp key: " . $d);
		$this->outputLine($d->getAbsolutePath());
		$this->outputLine(print_r(scandir(FLOW_PATH_DATA . '/Blob'), TRUE));
		Files::removeDirectoryRecursively($d->getAbsolutePath());

		$d = new Derivative('Cognifire.Example');
		$this->outputLine("with new pkg key: " . $d);
		$this->outputLine($d->getAbsolutePath());
		Files::removeDirectoryRecursively($d->getAbsolutePath());

		$d = new Derivative('Cognifire.Blob');
		$this->outputLine("with pkg key exists: " . $d);
		$this->outputLine(print_r($d->getAbsolutePath(), TRUE));
	}

	public function newBlobQueryCommand() {
		$d = new BlobQuery('Cognifire.Blob','Configuration/','text/plain');
		$this->outputLine(print_r($d->introspect(), TRUE));
	}
}

?>