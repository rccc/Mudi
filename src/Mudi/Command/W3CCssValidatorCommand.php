<?php

namespace Mudi\Command;

use Mudi\Command\MudiCommand;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class W3ccssValidatorCommand extends MudiCommand
{
	protected function configure()
	{
		$this
		->setName('css-validator')
		->setDescription('Validation CSS avec le validateur W3C')
		->addArgument(
			'name',
			InputArgument::REQUIRED,
			""
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

        /*
        $resource = new \Mudi\Resource($name);

        $service = new \Mudi\Service\Validator\W3CCssValidatorService();

        $this->results = $service->validate(file_get_contents($resource->path));
		*/

        $service = new \Mudi\ProxyService\W3CCssValidatorProxyService(array('resource_name'=>$name));

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
		foreach($this->results->all() as $result)
		{
			if(!$result->isValid)
			{
				$output->writeln(sprintf('<error>%s</error>', $result->status));

				if(!empty($result->errors))
				{
					foreach($result->errors as $error)
					{
						$output->writeln($error);
					}
				}
			}
			else{
				$output->writeln(sprintf('<info>%s</info>', $result->status));
			}

			$output->writeln(sprintf('Statut : %s, nombre erreur(s) :%d , nombre warnings : %d', $result->status, $result->error_count, $result->warning_count));
		}	
	}

	protected function HtmlOutput(OutputInterface $output)
	{
		$twig = $this->getApplication()->getService('twig');
		echo $twig->render('validation-w3c.html.twig', array('results'=>$this->results->all()));
	}

}