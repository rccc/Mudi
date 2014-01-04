<?php

namespace Mudi\Command;

use Mudi\Command\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Process;
use Neutron\ProcessManager\ProcessManager;


class RunAllCommand extends MudiCommand
{
	protected function configure()
	{
		$this
		->setName('run:all')
		->setDescription('execute les tests pour un lot d\'archives')
		->addArgument(
			'input_dir',
			InputArgument::REQUIRED,
			"nom du dossier contenant les archives à valider"
			)
		->addArgument(
			'output_dir',
			InputArgument::REQUIRED,
			'Où sauvegarder le fichier de résultats (/home/resultats/)'   
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{

		$output->writeln(sprintf('Executing %s ...', $this->getName()));

		$input_dir = $input->getArgument('input_dir');
		$output_dir = $input->getArgument("output_dir"); 

		if(!is_readable($input_dir) || !is_readable($output_dir))
		{
			throw new \Exception('Vérifier le chemin des dossiers en paramètres');
		}

		//recherche "zip"
		$finder = new Finder();        
		$finder->files()->in($input_dir)->name('*.zip');

		foreach ($finder as $file) {

			$output->writeln( $file->getFileName());
		}

		if(count($finder) === 0)
		{
			throw new \Exception( sprintf('%s : Aucune archive de type "zip" trouvée', $input_dir) );			
		}

		//process queue
		$manager = new ProcessManager();
		$manager->setMaxParallelProcesses(2);

		$processList = array();

		foreach ($finder as $file) {

			$output->writeln('Executing test for ' . $file->getFileName());

			$slug = \Mudi\Resource::slugify($file->getFileName());

			//on récupère juste le nom sans l'extension ( basename )
			//$archive_name = substr($file->getFileName(), 0 , strpos($file->getFileName(), '.'));

			//création nouveau dossier 
			$new_path = $output_dir .DS . $slug;
			if(!file_exists($new_path) && !mkdir($new_path))
			{
				throw new \Exception("Impossible d'écrire dans le dossier de sortie");
			}

			//commande
			$cmd = sprintf('php %sconsole.php run %s %s', BASE_PATH . DS, $file->getPathName(), $new_path);
			$manager->add(new Process($cmd));

		}

		$manager->run();

		foreach ($processList as $process) {
			if (!$process->isSuccessful()) {
				$output->writeln( sprintf("<error>%s</error>", $process->getErrorOutput() ) );
			}
			else{
				echo $process->getOutput();
			}
		}

		$output->writeln('<info>DONE</info>');

	}

}