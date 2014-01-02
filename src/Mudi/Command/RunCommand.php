<?php

namespace Mudi\Command;

use Mudi\Command\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;


class RunCommand extends MudiCommand
{
		protected function configure()
		{
			$this
				->setName('run')
				->setDescription('Validation W3c')
				->addArgument(
						'name',
						InputArgument::REQUIRED,
						"nom du fichier, du dossier ou de l'archive à valider"
				)
			    ->addArgument(
			        'output',
			        InputArgument::REQUIRED,
			        'Où sauvegarder le fichier de résultats (/home/resultats/)'   
			    )
			;
		}

		protected function execute(InputInterface $input, OutputInterface $output)
		{

			$name = $input->getArgument('name');
			
			$outputDirectory = $input->getArgument("output"); 
	
			$commands = array(
				'validation Tidy'           => array('validate:tidy' => array()),
				'validation W3C'			=> array('validate:w3c' => array()),
				'Vérification des liens' 	=> array('check-link'=> array()),
				'Stats balises utilisées' 	=> array('tag:stats' => array()),
				'Screenshot'				=> array('casperjs:screenshot' => array($outputDirectory))
			
				)
			;


			$tmp = array();
			$processList = array();
			$manager = new \Neutron\ProcessManager\ProcessManager();
			
			foreach($commands as $commandName => $data)
			{
				static $i = 0;
				$command = key($data);
				$args = $commands[$commandName][$command];

				$cmd = sprintf('php %sconsole.php %s %s %s --quiet --output-html', BASE_PATH . DS, $command, $name, implode(' ', $args));

				$output->writeln($cmd);

				$processList[$i] = new Process($cmd);
				
				$manager->add($processList[$i]);

				$i++;
			}

			$manager->run();

			foreach ($processList as $process) {
				if (!$process->isSuccessful()) {
 	   				echo $process->getErrorOutput();
				}
				else{
					$tmp[] = $process->getOutput();
				}
			}

			$twig = $this->getApplication()->getService('twig');
			$html = $twig->render('index.html.twig', array(
    			'content' => implode(PHP_EOL, $tmp)
    		));
			
			$fs = new \Symfony\Component\Filesystem\Filesystem();

			if(!$fs->exists($outputDirectory))
			{
				$output->writeln("<error>Le chemin de sortie n'existe pas</error>");
			}

			try
			{
				$pathinfos = pathinfo($name);
				$file = $outputDirectory . DS  . $pathinfos['basename'] . "-resultats.html";
				$fs->touch($file);
				file_put_contents($file,$html);
			}
			catch(Exception $e)
			{
				$output->writeln(sprintf("<error>impossible de créer le fichier de sortie : %s</error>", $e->getMessage()));
			}

			$output->writeln('DONE');

		}

}