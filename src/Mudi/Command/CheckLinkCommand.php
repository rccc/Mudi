<?php

namespace Mudi\Command;

use Mudi\Command\MudiCommand;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CheckLinkCommand extends MudiCommand
{
	protected $curl;
	protected $currentHref 		= null;
	protected $linkList 		= array();
	protected $completedList 	= array();
	protected $DOMDocumentError = array();

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

		$this->checkResource($name);

		if($this->resource->isHtml)
		{
			$this->getLinkList($this->resource->path);
		}
		elseif($this->resource->isDir)
		{
			$this->getDeepLinkList($this->resource->path);
		}
		elseif($this->resource->isArchive && $this->resource->isZip)
		{
			$tmp = $this->createTmpDir($this->resource);
			$this->getDeepLinkList($tmp);
			$this->removeTmpDir($tmp);
		}
		$this->validate();

		if($input->getOption('output-html'))
		{
			$this->HtmlOutput($output);
		}
		else
		{
			$this->consoleOutput($output);				
		}

	}

	protected function getLinkList($path)
	{

		libxml_use_internal_errors(true);

		$doc = new \DOMDocument();

		if(!$doc->loadHTMLFile($path))
		{
			foreach (libxml_get_errors() as $error) {
				$this->DOMDocumentError[] = sprintd('libxml error : %', $error); 
			}

			libxml_clear_errors();
		}
		else
		{
			$linkList = $doc->getElementsByTagName('a');
			if($linkList->length == 0)
			{
				$this->DOMDocumentError[] = sprintf("%s : Aucune balise trouvée dans le document !", $path);
			}
			else
			{
				foreach($linkList as $node)
				{

					$href = $node->getAttribute('href');
					if(empty($href)){
						$this->DOMDocumentError[] = 'un lien dont la valeur de l\'attribut "href" est vide a été trouvé dans le document';
					}
					else
					{
						$link = new \Mudi\Link();

						if(false !== strpos($href, 'http') )
						{
							$link->isRemote = true;
							$link->url   = $href;
						}else 
						{
							$link->isRemote = false;
							//on prends le "resource->path" ( var/www/leaflet/index.html )
							//on substitue la référence au fichier par la valeur de l'attribut
							$chunks = explode('/', $this->resource->path);
							array_pop($chunks);
							$chunks[] = substr($href,1);
							$link->url = implode('/', $chunks);							
						}

						$this->currentResource = $path;							
						$this->resource->results[$this->currentResource][$this->getName()][] = $link;
					}

				}//foreach

			}				
		}

	}


	protected function getDeepLinkList($path)
	{
		$dir = new \RecursiveDirectoryIterator($path);
		$it = new \RecursiveIteratorIterator($dir);

			//max Depth @todo -> config
		$it->setMaxDepth(2);

		$filtered = new \RegexIterator($it, '/^.+\.html?$/i', \RecursiveRegexIterator::GET_MATCH);			

		foreach ($filtered as $index => $file) 
		{
			$this->getLinkList($file[0]);	
					//max file @todo -> config
			if($index > 20) break;
		}

	}

	protected function validate()
	{
		if(!empty($this->currentResource) && !empty($this->resource->results[$this->currentResource]))
		{

			$ref = &$this->resource->results[$this->currentResource][$this->getName()];

			foreach($ref as $index => $link)
			{
				$link->isValid = false;
				if($link->isRemote)
				{
					$curl = $this->getCurl();
					$curl->get($link->url)->execute(array('link'=>$link));	
				}
				else{
					if(file_exists($link->url))
					{
						$link->exists = true;
						$this->completedList[] = $link; 
					}
				}
			}
				//@see curl callback
			$ref = $this->completedList;
		}	
	}

	protected function getCurl()
	{
		if(empty($this->curl)){

			$curl_options = array(

				CURLOPT_FAILONERROR => true,
				CURLOPT_NOBODY => true,
				CURLOPT_RETURNTRANSFER => true
				);

			$this->curl = new \RollingCurl\RollingCurl();

			$this->curl
			->setSimultaneousLimit(10)
			->setOptions($curl_options)
			->setCallback(function(\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl, $options) {

					$error = $request->getResponseError(); //todo : message correspondant au code http ? 
					$infos = $request->getResponseInfo();
					$http_code = (int) $infos['http_code'];

					$options['link']->exists = false;

					if(!empty($error))
					{
						$options['link']->error = $error;
						$this->completedList[] = $options['link'];
					}
					elseif(!($http_code >= 200 && $http_code < 400))
					{
						$options['link']->exists = false;
					}
					else
					{
						$options['link']->exists = true;	
					}								
					
					$this->completedList[] = $options['link'];
				})
			;

		}

		return $this->curl;
	}

	protected function consoleOutput(OutputInterface $output)
	{

		if(empty($this->DOMDocumentError) )
		{
			$output->writeln("Résultats pour : " . $this->currentResource);

			foreach($this->resource->results[$this->currentResource][$this->getName()] as $link)
			{
				if(!empty($link->error) || !$link->exists)
				{
					$output->writeln(sprintf('<bg=red>%s : %s</bg=red>', $link->url, $link->error));
				}
				elseif($link->exists)
				{
					$output->writeln(sprintf('<bg=green>%s : OK</bg=green>', $link->url));
				}
			}

		}
		else
		{

		}

	}


	protected function HtmlOutput(OutputInterface $output)
	{
	
		$tmp = array();
		$tmp[] = '<section class="command-section">';
		$tmp[] = '<h2>Résultats vérification des liens</h2>';

		if(!empty($this->DOMDocumentError))
		{
			foreach($this->DOMDocumentError as $error)
			{
				$tmp[] = sprintf('<p class="error">%s</p>', $error);				
			}
		}
		else{
			foreach($this->resource->results as $resource => $command)
			{
				foreach($command as $commandName => $results)
				{
					foreach($results as $result)
					{
						if(!empty($link->error) || !$link->exists)
						{
							$tmp[] = sprintf('<p class="error">%s : %s</p>', $link->url, $link->error);
						}
						elseif($link->exists)
						{
							$tmp[] = sprintf('<p class="success">%s : OK</p>', $link->url);
						}
					}
				}
			}
		}
		$tmp[] = '</section>';
		echo implode(PHP_EOL, $tmp);
	

	}
}