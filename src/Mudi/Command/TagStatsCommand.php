<?php

namespace Mudi\Command;

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

        $service = new \Mudi\ProxyService\TagUsageProxyService(array('resource_name'=>$name));

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

		foreach ($this->results->all() as $fileName => $result) {

			$tmp = array();

			$output->writeln("Résultats pour : " . $fileName);

			foreach($result->stats as $tagName => $count)
			{
				$tmp[] = sprintf("%s %s=> %d", $tagName,str_repeat("\t", 2), $count);
			}
			print implode(PHP_EOL, $tmp) . PHP_EOL;

		}

		$output->writeln( sprintf('<info>%s DONE</info>', $this->getName()) );
	}

	protected function HtmlOutput(OutputInterface $output)
	{
		$twig = $this->getApplication()->getService('twig');
		
		print $twig->render('tag_usage.html.twig', array('results' => $this->results->all()));

	}
}