<?php

namespace Mudi\Command;

use Mudi\Command\MudiCommand;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TidyCommand extends MudiCommand
{
	protected function configure()
	{

		$this
		->setName('validate:tidy')
		->setDescription('Validation HTML avec Tidy')
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

        $service = new \Mudi\ProxyService\TidyProxyService(array('resource_name'=>$name));

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
		if($this->results instanceof \Mudi\Collection\OutputCollection)
		{
			foreach($this->results->all() as $file_path => $result)
			{
				
				$output->writeln('Résultats Tidy pour : ' . $file_path);

				if($result->status === 0)
				{
					$output->writeln('<info>Valide</info>');
				}
				elseif($result->status === 1)
				{
					$output->writeln('<bg=yellow;fg=white>Avertissements</bg=yellow;fg=white>');
				}
				elseif($result->status === 2)
				{
					$output->writeln('<error>Non valide</error>');
				}

				if(!empty($result->errors))
				{
					foreach($result->errors as $error)
					{
						$output->writeln($error);
					}					
				}

				if(!empty($result->infos))
				{
					foreach($result->infos as $info)
					{
						$output->writeln($info);
					}
				}

				$output->writeln(sprintf("statut : %s", $result->status));
				$output->writeln(sprintf("erreurs : %s", $result->count_errors));
				$output->writeln(sprintf("accessibilité : %s", $result->count_errors));

			}
		}

	}

	protected function HtmlOutput(OutputInterface $output)
	{
		$twig = $this->getApplication()->getService('twig');
		echo $twig->render('tidy.html.twig', array('results'=>$this->results->all()));
	}
}