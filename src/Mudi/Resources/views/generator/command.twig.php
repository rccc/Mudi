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

	}

}