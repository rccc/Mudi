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

		$this->checkResource($name);

		if($this->resource->isHtml){
			$this->getTagStats($this->resource->path);
		}
		elseif($this->resource->isDir)
		{
			$this->getDeepTagStats($this->resource->path);				
		}
		elseif($this->resource->isArchive && $this->resource->isZip)
		{
			$tmp = $this->createTmpDir($this->resource);
			$this->getDeepTagStats($tmp);
			$this->removeTmpDir($tmp);
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

	protected function getTagStats($resource) {

		$this->currentResource = $resource;

		libxml_use_internal_errors(true);

		$doc = new \DOMDocument();

		if(!$doc->loadHTMLFile($resource))
		{
			foreach (libxml_get_errors() as $error) {
				var_dump('libxml_error', $error);
			}

			libxml_clear_errors();
		}
		else{
			$tagList = $doc->getElementsByTagName('*');
			if($tagList->length == 0)
			{
				var_dump("Aucune balise trouvée dans le document ?!");
			}
			else{

				$count_list = array();
				foreach($tagList as $tag)
				{
					$tagName = $tag->tagName;
					if(in_array($tagName, array_keys($count_list))){
						$count_list[$tagName]++;
					}
					else{
						$count_list[$tagName] = 1;
					}
				}
			}

			$this->resource->results[$this->currentResource][$this->getName()] = $count_list;

		}

	}

	protected function getDeepTagStats($path)
	{
		$dir = new \RecursiveDirectoryIterator($path);
		$it = new \RecursiveIteratorIterator($dir);

			//max Depth @todo -> config
		$it->setMaxDepth(2);

		$filtered = new \RegexIterator($it, '/^.+\.html?$/i', \RecursiveRegexIterator::GET_MATCH);			

		foreach ($filtered as $index => $file) 
		{
			$this->getTagStats($file[0]);	
					//max file @todo -> config
			if($index > 20) break;
		}

	}

	protected function consoleOutput(OutputInterface $output)
	{

		foreach ($this->resource->results as $resource => $commandName) {

			$tmp = array();

			$output->writeln("Résultats pour : " . $resource);

			foreach ($commandName as $result) {
				foreach($result as $tagName => $count)
				{

					$tmp[] = sprintf("%s %s=> %d", $tagName,str_repeat("\t", 2), $count);
				}
				print implode(PHP_EOL, $tmp);

			}

		}

		print str_repeat(PHP_EOL, 2);

	}

	protected function HtmlOutput(OutputInterface $output)
	{

        $tmp = array();
        $tmp[] = '<section class="command-section">';
        $tmp[] = sprintf('<h2>%s</h2>', "Résultats Statistiques balises");

		foreach ($this->resource->results as $resource => $commandName) 
		{
						
			$tmp[] = sprintf("<h3>Résultats pour : %s</h3>", $resource);
	
			foreach ($commandName as $result) {
				$tmp[] = "<table>";
				foreach($result as $tagName => $count)
				{
					$tmp[] = sprintf("<tr><td>%s</td><td>%d</td></tr>", $tagName, $count);
				}
				$tmp[] = "</table>";

			}
			
		}

		$tmp[] = '</section>';
		print implode(PHP_EOL, $tmp);

	}
}