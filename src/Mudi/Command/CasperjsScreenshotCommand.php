<?php

namespace Mudi\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class CasperjsScreenshotCommand  extends MudiCommand
{

	protected function configure()
	{
		$this
		->setName('casperjs:screenshot')
		->setDescription('retourne un screenshot')
		->addArgument(
			'name',
			InputArgument::OPTIONAL,
			"nom du fichier HTML"
			)
		->addArgument(
			'output',
			InputArgument::OPTIONAL,
			"dossier dans lequel seront sauvegardés les screenshots"
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
		$output_dir = $input->getArgument('output');

        $output->writeln(sprintf('Executing %s for %s', $this->getName(), $name));
		
		$service = new \Mudi\ProxyService\ScreenshotProxyService(array('resource_name'=>$name, 'output_dir' => $output_dir));
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


	public function consoleOutput(OutputInterface $output)
	{
		foreach ($this->results->all() as $fileName => $result) {
			$output->writeln($result->message);
		}
	}

	public function HtmlOutput()
	{
		$twig = $this->getApplication()->getService('twig');

		print $twig->render('screenshot.html.twig', array("results" => $this->results->all()));
	}


}