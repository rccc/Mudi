<?php

namespace Mudi\Command;

use Mudi\Command\MudiCommand;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CheckLinkCommand extends MudiCommand
{
	protected $linkChecker;

	protected function configure()
	{
		$this
		->setName('check-link')
		->setDescription('Vérifie la validité des liens contenus dans une page')
		->addArgument(
			'name',
			InputArgument::OPTIONAL,
			"nom du fichier, du dossier ou de l'archive à analyser"
			)
		->addOption(
			'output-html',
			null,
			InputOption::VALUE_NONE,
			'output html'

			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$name = $input->getArgument('name');

		$output->writeln(sprintf('Executing %s for %s', $this->getName(), $name));

		$service = new \Mudi\ProxyService\LinkCheckerProxyService(array('resource_name'=>$name));

		$this->results = $service->execute();
		


		if($input->getOption('output-html'))
		{
			$this->HtmlOutput($output);
		}
		else
		{
			$this->consoleOutput($output);				
		}

	}

	protected function consoleOutput(OutputInterface $output)
	{

		foreach($this->results->all() as $documentPath => $result)
		{
			$output->writeln("Résultats pour : " . $documentPath);
			
			if(!empty($result->errors))
			{
				foreach($result->errors as $error)
				{
					$output->writeln(sprintf('<bg=cyan> %s <bg=cyan><bg=red> %s </bg=red>', $documentPath, $error));
				}
			}
			else
			{		
				foreach($result->urls as  $link)
				{	
					if(!empty($link->error) || !$link->exists)
					{
						$output->writeln(sprintf('<bg=cyan> %s <bg=cyan><bg=red> %s </bg=red>', $link->raw_url, $link->error));
					}
					elseif($link->exists)
					{
						$output->writeln(sprintf('<bg=cyan> %s <bg=cyan><bg=green> OK </bg=green>', $link->url));
					}
					
				}
			}
		}

	}




	protected function HtmlOutput(OutputInterface $output)
	{

		$twig = $this->getApplication()->getService('twig');
		
		print $twig->render('check_link.html.twig', array('results' => $this->results->all(), 'errors' => array()));

	}
}