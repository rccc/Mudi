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
					 'yell',
					 null,
					 InputOption::VALUE_NONE,
					 'Si défini, la réponse est rendue en majuscules'
				)
				->addOption(
					 'file',
					 null,
					 InputOption::VALUE_NONE,
					 'Si défini, valide un fichier html'
				)
			;
		}

		protected function execute(InputInterface $input, OutputInterface $output)
		{
			
			$name = $input->getArgument('name');
			$this->checkResourceAndValidate($name);
			$this->consoleOutput($output);

		}

		protected function validate($resource) 
		{

			$this->currentResource = $resource;

			$header_size = 0;
			$options = array(
				CURLOPT_HEADER => true,
				CURLOPT_USERAGENT => "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)",
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => array('uploaded_file' => file_get_contents($this->currentResource), 'output' =>'json', 'debug'=>1, 'verbose'=> 1),
				CURLOPT_RETURNTRANSFER => true,
			);

			$this->curl
				->setSimultaneousLimit(10)
				->setOptions($options)
				->post('http://validator.w3.org/check')
				->setCallback(function(\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl) {
        			
					$responseErrors = $request->getResponseError();

					if(empty($responseErrors))
					{
	        			echo "Fetch complete for (" . $this->currentResource . ")" . PHP_EOL;

	        			$header_size = $request->getResponseInfo()['header_size'];
						$header 	 = substr($request->getResponseText(), 0, $header_size);
						$body 		 = substr($request->getResponseText(), $header_size);
				
						preg_match('/X-W3C-Validator-Status:\s([a-zA-Z]+)/', $header, $result);
						list(,$status) 	= $result; 
						preg_match('/X-W3C-Validator-Errors:\s(\d+)/', $header, $result);
						list(,$errors)   = $result;
						preg_match('/X-W3C-Validator-Warnings:\s(\d+)/', $header, $result);
						list(,$warnings) = $result;
						//@todo "abort status", "recursion"

						$this->resource->results[$this->currentResource][$this->getName()] = array(
							'url'   		=> $this->currentResource,
							'response_body' => json_decode($body, true),
							'status' 		=> $status,
							'errors' 		=> $errors,
							'warnings' 		=> $warnings

						);

					}
					else
					{
						$this->resource->results[$this->currentResource][$this->getName()] = false;
					}

    			})
    			->execute()
    		;

		}

		protected function deepValidate($path)
		{
			$dir = new \RecursiveDirectoryIterator($path);
			$it = new \RecursiveIteratorIterator($dir);
			
			//max Depth @todo -> config
			$it->setMaxDepth(2);

			$filtered = new \RegexIterator($it, '/^.+\.html?$/i', \RecursiveRegexIterator::GET_MATCH);			
			
			foreach ($filtered as $index => $file) 
			{
					$this->validate($file[0]);	
					//max file @todo -> config
					if($index > 20) break;
			}

		}

		protected function consoleOutput(OutputInterface $output)
		{

			foreach($this->resource->results as $resource)
			{
				foreach($resource as  $commandName => $result)
				{


					if(!$result) continue;

					if($result['status'] === "Valid")
					{
						$output->writeln(sprintf('<bg=green>%s : Valide</bg=green>', $result['url']));
						$output->writeln(sprintf('Encodage détécté : %s ', $result['response_body']['source']['encoding']));

					}
					else
					{
						$output->writeln(sprintf('<error>%s : Non valide</error>', $result['url']));

						foreach ($result['response_body']['messages'] as $value) {
							$output->writeln(sprintf('<error>%s</error>', $value['message']));						
						}
						
						$output->writeln(sprintf('<comment>nombre erreurs : %s</comment>', $result['errors']));						

						if(!empty($result['warnings']))
						{
							$output->writeln(sprintf('<comment>avertissements: %s</comment>', $result['warnings']));						
						}

					}
				}
			}

			print str_repeat(PHP_EOL, 2);
		}
}