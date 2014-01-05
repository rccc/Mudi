<?php

namespace Mudi\Command;

use Mudi\Command\MudiCommand;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends MudiCommand
{
	protected function configure()
	{

		$this
		->setName('generate')
		->setDescription('<<<EOF
			permets de générer une nouvelle commande, un nouveau service
			EOF')
		->addArgument(
			'name',
			InputArgument::REQUIRED,
			"Nom de la commande ou du service"
			)
		->addOption(
			'command',
			null,
			InputOption::VALUE_NONE,
			'nouvelle commande'
			)
		->addOption(
			'service',
			null,
			InputOption::VALUE_NONE,
			'nouvelle commande'
			)
		->addOption(
			'desc',
			null,
			InputOption::VALUE_REQUIRED,
			'description ( commande uniquement)'
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$name = $input->getArgument('name');

		$twig = $this->getApplication()->getService('twig');

		if($command = $input->getOption('command'))
		{	
			$command_path = MUDI_PATH . DS . 'Command' . DS . ucfirst($name) . 'Command.php';
			
			if(file_exists($command_path))
			{
				$output->writeln('<error>Une commande du même nom existe déjà<error>');
				die('ABORT');
			}

			$desc = $input->getOption('desc');

			try{
				$content = $twig->render('command.twig.php', array('command_name' => $name, 'description' => $desc));
				file_put_contents( $command_path, $content);		
			}
			catch(\Exception $e)
			{
				$output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
			}

		}
		elseif($service = $input->getOption('service'))
		{
		}
		else{
			$output->writeln('<error>Option manquante : php console generate --help <error>');
			die('ABORT');			
		}

		$output->writeln('<info>DONE</info>');

	}

}