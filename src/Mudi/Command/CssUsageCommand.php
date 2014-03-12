<?php

namespace Mudi\Command;

use Mudi\Command\MudiCommand;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CssUsageCommand extends MudiCommand
{
	protected function configure()
	{

		$this
		->setName('css-usage')
		->setDescription('Usage de règles CSS')
		->addArgument(
			'name',
			InputArgument::OPTIONAL,
			"Nom du fichier CSS à examiner"
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

		$service = new \Mudi\ProxyService\CssUsageProxyService(array('resource_name'=>$name));

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
		foreach($this->results->all() as $document_name => $result)
		{
			if($result->media_query_count > 0)
			{
				$output->writeln(sprintf('<info>Media queries : %d</info>', $result->media_query_count));
			}
			else 
			{
				$output->writeln('<error>Aucune règles "media queries"</error>');
			}

			if($result->css3_count > 0)
			{

				$output->writeln(sprintf('<info>Ce document contient %d propriété(s) CSS3</info>', $result->css3_count));


				if($result->css3_no_vendor > 0)
				{
					$output->writeln(sprintf('<comment>Ce document contient %d propriété(s) CSS3 sans "vendor prefix"</comment>', $result->css3_no_vendor));
				}

			}
			else 
			{
				$output->writeln('<error>Ce document ne contient pas de propriété(s) CSS3 "</error>');
			}

		}
	}

	protected function HtmlOutput(OutputInterface $output)
	{
		$twig = $this->getApplication()->getService('twig');
		echo $twig->render('css_usage.html.twig', array('results'=>$this->results->all()));
	}

}