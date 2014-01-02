<?php

namespace Mudi\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class CasperjsCommand  extends MudiCommand
{

	protected function configure()
	{

		$this
		->setName('casperjs:run')
		->setDescription('execute un script Casperjs')
		->addArgument(
			'name',
			InputArgument::OPTIONAL,
			"nom du script"
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

        $res = shell_exec(sprintf('casperjs test %s', $name));
		
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
	}

}