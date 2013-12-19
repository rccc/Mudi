<?php

namespace Mudi\Command;

use Mudi\Command\MudiCommand;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class tagStatsCommand extends MudiCommand
{
	protected function configure()
	{

		$this
		->setName('tag:stats')
		->setDescription('Statistiques des balises HTML utilisées')
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

		$this->resource  = new \Mudi\Resource($name);
        $this->tagUsage = new \Mudi\TagUsage();  

        $files = $this->resource->getResourceFilesContent('html?');

        foreach($files as $fileName => $fileContent)
        {
        	$this->resource->results[$fileName] = $this->tagUsage->getUsageStats($fileContent);
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

		foreach ($this->resource->results as $fileName => $result) {

			$tmp = array();

			$output->writeln("Résultats pour : " . $fileName);

			foreach($result as $tagName => $count)
			{

				$tmp[] = sprintf("%s %s=> %d", $tagName,str_repeat("\t", 2), $count);
			}
			print implode(PHP_EOL, $tmp);
			print PHP_EOL;
		}


	}

	protected function HtmlOutput(OutputInterface $output)
	{

        $tmp = array();
        $tmp[] = '<section class="command-section">';
        $tmp[] = sprintf('<h2>%s</h2>', "Résultats Statistiques balises");
		$tmp[] = '<div class="section-body">';

		foreach ($this->resource->results as $fileName => $result) 
		{
			$tmp[] = '<div class="result">';			
		    $tmp[] = sprintf('<div class="resource-name label default">%s</div>', $fileName);
	
			$tmp[] = "<table>";
			foreach($result as $tagName => $count)
			{
				$tmp[] = sprintf("<tr><td>%s</td><td>%d</td></tr>", $tagName, $count);
			}
			$tmp[] = "</table>";

			$tmp[] = '</div><!-- .result -->';
			
		}

		$tmp[] = '</div>';
		$tmp[] = '</section>';
		print implode(PHP_EOL, $tmp);

	}
}