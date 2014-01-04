<?php

namespace Mudi\Command;

use Mudi\Command\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;

class RenameResourceCommand extends MudiCommand
{
	protected function configure()
	{
		$this
		->setName('rename')
		->setDescription('renomme un ensemble de fichiers')
		->addArgument(
			'input_dir',
			InputArgument::REQUIRED,
			"nom du dossier contenant les archives à renommer"
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{

		$output->writeln(sprintf('Executing %s ...', $this->getName()));

		$input_dir = $input->getArgument('input_dir');

		if(!is_dir($input_dir))
		{
			throw new \Exception('Vérifier le chemin des dossiers en paramètres');
		}

		$fs = new Filesystem();

		$finder = new Finder();        
		$finder->files()->in($input_dir)->name('*.txt');

		if(empty($finder))
		{
			throw new \Exception('Aucune archive trouvée');			
		}


		foreach ($finder as $file) {

			$old_name = $file->getPathName();
			$new_name = $this->slugify($old_name);
			$output->writeln('renommage %s ... ' . $old_name);



			try {
				//$fs->rename($old_name, $new_name);
			} catch (IOException $e) {
				echo "Impossible de renommer le fichier";
			}

			$output->writeln('<info>%s</info>' . $new_name);

		}

		$output->writeln(sprintf('<info>%s DONE</info>', $this->getName()));

	}


	public function slugify($text) 
	{ 
		$text = preg_replace('~[^\\pL\d]+~u', '-', $text); 
		$text = trim($text, '-'); 
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text); 
		$text = strtolower($text); 
		$text = preg_replace('~[^-\w]+~', '', $text); 
		echo $text; 
		if (empty($text)) 
		{ 
			return 'n-a'; 
		} 

		return $text; 
	} 

}