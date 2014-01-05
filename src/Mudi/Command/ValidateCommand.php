<?php

namespace Mudi\Command;

use Mudi\Command\BaseValidateCommand;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ValidateCommand extends BaseValidateCommand
{
	protected function configure()
	{
		$this->curl = new \RollingCurl\RollingCurl();

		$this
		->setName('validate:w3c')
		->setDescription('Validation W3c')
		->addArgument(
			'name',
			InputArgument::OPTIONAL,
			"nom du fichier, du dossier ou de l'archive à valider"
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

		$service = new \Mudi\ProxyService\W3CMarkupValidatorProxyService($name);

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

		foreach($this->results->all() as $fileName => $result)
		{

			$output->writeln(sprintf('Résultats pour : %s', $fileName));

			if(!empty($result->status) && $result->status === "Abort")
			{
				$output->writeln(sprintf('<error>status: le document a été ignoré</error>', $result->status));						
				$output->writeln(sprintf('<error>status: %s</error>', $result->status));						
				return;
			}	

			if($result->isValid)
			{
				$output->writeln(sprintf('<bg=green>%s : Valide</bg=green>', $fileName));
				$output->writeln(sprintf('Encodage détécté : %s ', $result->encoding));

			}
			else
			{
				$output->writeln('<error>Non valide</error>');

				foreach ($result->messages as $value) {
						//var_dump($value);
					$output->writeln(sprintf('<comment>message : %s</comment>', $value['message']));						
				}

				$output->writeln(sprintf('<comment>nombre erreurs : %s</comment>', $result->errors));						

			}

			if(!empty($result->warnings))
			{
				$output->writeln(sprintf('<comment>avertissements: %s</comment>', $result->warnings));						
			}


		}


		print str_repeat(PHP_EOL, 2);
	}


	protected function HtmlOutput(OutputInterface $output)
	{
		$twig = $this->getApplication()->getService('twig');

		print $twig->render('validation-w3c.html.twig', array('results'=> $this->results->all()));

        }
    }