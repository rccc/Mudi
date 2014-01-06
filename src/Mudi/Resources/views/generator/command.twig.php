<?php

namespace Mudi\Command;

use Mudi\Command\MudiCommand;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class {{ command_name|capitalize }}Command extends MudiCommand
{
	protected function configure()
	{

		$this
		->setName('{{ command_name }}')
		->setDescription('{{ description }}')
		->addArgument(
			'name',
			InputArgument::OPTIONAL,
			""
			)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$name = $input->getArgument('name');

		{% if with_proxy %}

        $output->writeln(sprintf('Executing %s for %s', $this->getName(), $name));

        $service = new \Mudi\ProxyService\{{ command_name|capitalize }}Service($name);

        $this->results = $service->execute();
		
		if($input->getOption('output-html'))
		{
			$this->HtmlOutput($output);
		}
		else
		{
			$this->consoleOutput($output);				
		}	

		{% endif %}
	}

{% if with_proxy %}
	protected function consoleOutput(OutputInterface $output)
	{		
	}

	protected function HtmlOutput(OutputInterface $output)
	{
		$twig = $this->getApplication()->getService('twig');
		echo $twig->render('{{ command_name }}.html.twig', array('results'=>$this->results->all()));
	}
{%  endif %}

}