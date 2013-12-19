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
		
		$this->resource  	= new \Mudi\Resource($name);
        $this->linkChecker 	= new \Mudi\Link\LinkChecker();
        

        foreach($this->resource->getUrls() as $documentPath => $urls)
        {
        	if (OutputInterface::VERBOSITY_NORMAL <= $output->getVerbosity()) {
    			$output->writeln('Document en cours : ' . $documentPath);
			}
			$this->resource->results[$documentPath] = $this->linkChecker->check($urls, $documentPath);
        }

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

		if(!empty($this->resource->results) )
		{

			foreach($this->resource->results as $documentPath => $links)
			{
				$output->writeln("Résultats pour : " . $documentPath);

				foreach($links as  $link)
				
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

		if(!empty($this->resource->errors))
		{

			$output->writeln(sprintf('<bg=red>Des erreurs sont survenues  : </bg=red>'));

			foreach($this->resource->errors as $error)
			{
				foreach($error as $error_key => $error_value)
				{
					$output->writeln(sprintf('<bg=cyan> %s <bg=cyan><bg=red> %s </bg=red>', $error_key, $error_value));
				}
			}
		}

	}


	protected function HtmlOutput(OutputInterface $output)
	{

		$tmp = array();
		$tmp[] = '<section class="command-section">';
		$tmp[] = '<h2>Résultats vérification des liens</h2>';
		$tmp[] = '<div class="section-body">';

		if(!empty($this->resource->results))
		{
			$tmp[] = '<h3>Lien(s) Valide(s)</h3>';

			foreach($this->resource->results as $fileName => $result)
			{
				$tmp[] = sprintf('<div class="resource-name label default">%s</div>', $fileName);

				foreach($result as $link)
				{

					$tmp[] = '<div class="result">';            

					if(!empty($link->error))
					{
						$tmp[] = sprintf('<p class="label error">%s </p>', $link->error);
					}
					elseif($link->exists)
					{
						$tmp[] = sprintf('<p class="label success">%s : OK</p>', $link->url);
					}

					$tmp[] = '</div><!-- .result -->';

				}
				
			}

			if(!empty($this->resource->errors))
			{
				foreach($this->resource->errors as $error)
				{
					foreach($error as $key_error => $value_error)
					$tmp[] = '<div class="result">';            
					$tmp[] = sprintf('<p class="label error">%s : %s </p>',$key_error,$value_error);
					$tmp[] = '</div><!-- .result -->';
				}
			}
		}

		$tmp[] = '</div>';
		$tmp[] = '</section>';
		echo implode(PHP_EOL, $tmp);

	}
}