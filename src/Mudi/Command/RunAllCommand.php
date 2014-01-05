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

		$services = array(
			'validation Tidy'   		 => array(
				'ProxyService' => '\Mudi\ProxyService\TidyProxyService',
				'template' => 'tidy.html.twig'),
			'validation W3C'			=> array(
				'ProxyService' => '\Mudi\ProxyService\W3CMarkupValidatorProxyService',
				'template' => 'validation-w3c.html.twig'),
			'Vérification des liens' 	=> array(
				'ProxyService' => '\Mudi\ProxyService\LinkCheckerProxyService',
				'template' => 'check_link.html.twig'),
			'Stats balises utilisées' 	=> array(
				'ProxyService' => '\Mudi\ProxyService\TagUsageProxyService',
				'template' => 'tag_usage.html.twig'),
			'Screenshot'				=> array(
				'ProxyService' => '\Mudi\ProxyService\ScreenshotProxyService',
				'template' => 'screenshot.html.twig',
				'params'   => array('output_dir'))
			)
		;

		$input_dir = $input->getArgument('input_dir');
		$output_dir = $input->getArgument("output_dir"); 

		if(!is_dir($input_dir) || !is_dir($output_dir))
		{
			throw new \Exception('Vérifier le chemin des dossiers en paramètres');
		}

		$twig = $this->getApplication()->getService('twig');

		//recherche "zip"
		$finder = new Finder();        
		$finder->files()->in($input_dir)->name('*.zip');

/*
		foreach ($finder as $file) {

			$output->writeln( $file->getFileName());
		}
*/
		if(count($finder) === 0)
		{
			throw new \Exception( sprintf('%s : Aucune archive de type "zip" trouvée', $input_dir) );			
		}

		//process queue
		//$manager = new ProcessManager();
		//$manager->setMaxParallelProcesses(1);
		//$processList = array();


		foreach ($finder as $file) {

			$output->writeln('Fichier en cours de traitement : ' . $file->getFileName());

			$array = array();
			//on récupère juste le nom sans l'extension ( basename )
			//$archive_name = substr($file->getFileName(), 0 , strpos($file->getFileName(), '.'));

			//création nouveau dossier 
			$new_path = $output_dir . $file->getFileName();
			if(!file_exists($new_path) && !mkdir($new_path))
			{
				throw new \Exception("Impossible d'écrire dans le dossier de sortie");
			}

			$resource = new \Mudi\Resource($file->getPathName());
			//commande
			//$cmd = sprintf('php %sconsole.php run %s %s', BASE_PATH . DS, $file->getPathName(), $new_path);
			//$manager->add(new Process($cmd));

			foreach($services as $name => $data)
			{
				$output->writeln('Current Service : ' . $name);
				if($name === "Screenshot")
				{
					$service = new $data['ProxyService']('', $new_path,  $resource );	
				}
				else{
					$service = new $data['ProxyService']('', $resource );
				}
				$results  = $service->execute();
				$array[] = $twig->render($data['template'], array('results' => $results->all() )); 
			}

			$content = $twig->render('index.html.twig', array('content' => implode(PHP_EOL, $array)));
			$result_path = sprintf('%s/resultats-%s.html', $new_path, $resource->name);
			file_put_contents( $result_path , $content);

			try{
				$o_path = $new_path . DS . 'originaux';
				if(!is_dir($o_path))
				{
					mkdir($o_path);
				}
				$cmd = sprintf('cp -R %s/* %s/ ', $resource->archive_path, $o_path);
				shell_exec($cmd);

			}
			catch(\Exception $e)
			{
				$output->writeln( sprintf('<error>%s</error>', $e->getMessage()) );
			}

			$resource->delete_archive();

			print PHP_EOL;

		}

		//$manager->run();
		/*
		foreach ($processList as $process) {
			if (!$process->isSuccessful()) {
				$output->writeln( sprintf("<error>%s</error>", $process->getErrorOutput() ) );
			}
			else{
				echo $process->getOutput();
			}
		}
		*/
	

		$output->writeln('<info>DONE</info>');

	}


}