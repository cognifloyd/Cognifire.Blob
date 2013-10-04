<?php
namespace Cognifire\Filefish\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Cognifire.Filefish".        *
 *                                                                        *
 *                                                                        */

use Cognifire\Filefish\FlowQuery\FileOperations;
use Cognifire\Filefish\Domain\Model\Derivative;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\CommandController;
use TYPO3\Flow\Utility\Files;
use TYPO3\Flow\Utility\MediaTypes;

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
		$this->outputLine(print_r(scandir(FLOW_PATH_DATA . '/Filefish'), TRUE));
		Files::removeDirectoryRecursively($d->getAbsolutePath());

		$d = new Derivative('@humbug');
		$dir = FLOW_PATH_DATA . '/Filefish/@humbug';
		Files::createDirectoryRecursively($dir);
		$this->outputLine("with temp key: " . $d);
		$this->outputLine($d->getAbsolutePath());
		$this->outputLine(print_r(scandir(FLOW_PATH_DATA . '/Filefish'), TRUE));
		Files::removeDirectoryRecursively($d->getAbsolutePath());

		$d = new Derivative('Cognifire.Example');
		$this->outputLine("with new pkg key: " . $d);
		$this->outputLine($d->getAbsolutePath());
		Files::removeDirectoryRecursively($d->getAbsolutePath());

		$d = new Derivative('Cognifire.Filefish');
		$this->outputLine("with pkg key exists: " . $d);
		$this->outputLine(print_r($d->getAbsolutePath(), TRUE));
	}

	public function newBlobQueryCommand() {
		$d = new FileOperations('Cognifire.Filefish');
		$d->exclude('Classes')
		->exclude('/.*L.*/')
		;
		$this->outputLine(print_r($d->introspect(), TRUE));

		$d = new FileOperations('Cognifire.Filefish');
		$d->ofMediaType('text/html')
		;
		$this->outputLine(print_r($d->introspect(), TRUE));

		$d = new FileOperations('Cognifire.Filefish');
		$d->in('Classes/Cognifire/Filefish')
		->exclude('Package')
		->with('composer.json')
		;
		$this->outputLine(print_r($d->introspect(), TRUE));

		$d = new FileOperations('Cognifire.Filefish');
		$d->with('composer.json');
		$this->outputLine(print_r($d->introspect(), TRUE));
	}

	public function newBqTestCommand() {
		$d = new FileOperations('Cognifire.Filefish');
		$d->in('Resources');
		$this->outputLine(print_r($d->introspect(), TRUE));
		touch(FLOW_PATH_PACKAGES . 'Application/Cognifire.Filefish/Resources/testFile');
		$this->outputLine(print_r($d->introspect(), TRUE));
		unlink(FLOW_PATH_PACKAGES . 'Application/Cognifire.Filefish/Resources/testFile');
		$this->outputLine(print_r($d->introspect(), TRUE));
	}

	public function copyFilesTestCommand() {
		$d = new FileOperations('Cognifire.Filefish');
		touch(FLOW_PATH_PACKAGES . 'Application/Cognifire.Filefish/Resources/testFile');
		$d->from('Cognifire.Filefish')
			->integrateFiles(array(
				'Resources/testFile' => 'Resources/testFile2'
							 ));
		$files = array();
		/** @var $file \SplFileInfo */
		foreach($d->getFinder() as $file) {
			$files[] = $file->getPathname();
		}

		$this->outputLine(print_r($files, TRUE));

		unlink(FLOW_PATH_PACKAGES . 'Application/Cognifire.Filefish/Resources/testFile');
		unlink(FLOW_PATH_PACKAGES . 'Application/Cognifire.Filefish/Resources/testFile2');
	}
}

?>