<?php

namespace Mudi\Command;

use Mudi\Command\MudiCommand;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\Event;

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

		if($command = $input->getOption('command'))
		{	
			$this->generateCommand($name, $output);
		}
		elseif($service = $input->getOption('service'))
		{
			$service_path 	= MUDI_PATH . DS . 'Service' . DS . ucfirst($name) . 'Service.php';
			$proxy_path		= MUDI_PATH . DS . 'ProxyService' . DS . ucfirst($name) . 'ProxyService.php';
			$tpl_path		= MUDI_PATH . DS . 'views/' . DS . $name . '.html.twig.php';

			if(file_exists($command_path))
			{
				$output->writeln('<error>un Service du même nom existe déjà<error>');
				die('ABORT');
			}

			//creation classe service
			$content = $twig->render('service.twig.php', array('service_name' => $name));
			file_put_contents($service_path, $content);		

			//creation proxy service
			$content = $twig->render('service_proxy.twig.php', array('service_name' => $name));
			file_put_contents($proxy_path, $content);		
			
			//creation twig template
			file_put_contents($proxy_path, $content);	

			//creation commande
			$this->generateCommand($name, $output, true);

		}
		else{
			$output->writeln('<error>Option manquante : php console generate --help <error>');
			die('ABORT');			
		}

		$output->writeln('<info>DONE</info>');

	}

	private function generateCommand($name, $output, $with_proxy = false)
	{
		$twig = $this->getApplication()->getService('twig');

		$command_path = MUDI_PATH . DS . 'Command' . DS . ucfirst($name) . 'Command.php';

		if(file_exists($command_path))
		{
			$output->writeln('<error>Une commande du même nom existe déjà<error>');
			die('ABORT');
		}

		$desc = $input->getOption('desc');

		try{
			$content = $twig->render(
				'command.twig.php', 
				array(
					'command_name' => $name, 
					'description' => $desc, 
					'with_proxy'  => $with_proxy
					)
				);
			file_put_contents($command_path, $content);		
		}
		catch(\Exception $e)
		{
			$output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
		}		
	}

}