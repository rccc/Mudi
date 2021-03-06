<?php

namespace Mudi\Service\Link;

/**
 * vérifie la validité des liens trouvés dans un fichier 'HTML'
 */

class LinkCheckerService
{

	public $results;

	public function __construct()
	{
		$this->name = 'link_checker';
	}

	public function check($arg)
	{
		if(is_array($arg))
		{
			return $this->checkUrls($arg, $path);
		}
		elseif(is_string($arg))
		{
			return $this->checkDocument($path);
		}
		else{
			throw new Exception("Link checker : impossible de déterminer le type d'argument");
		}
	}

	public function checkDocument($document_path)
	{
		$this->result = new \Mudi\Result\LinkCheckerResult();

		$urls = $this->extractUrls($document_path);
		if(empty($urls))
		{
			$this->result->errors[] = 'aucun lien trouvé dans le document'; 
			return $this->result;
		}
		else
		{
			return $this->checkUrls($urls, $document_path);			
		}

	}

	public function checkUrls(Array $urls, $resource_path)
	{
		$total = 0;
		$broken = 0;

		foreach($urls as $url)
		{			

			$link = new \Mudi\Service\Link\Link();
			$link->raw_url = $url;


			//empty href attribute ?
			if(empty($url)){
				$link->error = 'Un lien dont la valeur de l\'attribut "href" est vide a été trouvé dans le document';
			}
			elseif(0 === strpos($url, 'mailto'))
			{
				//on considère que le lien existe
				$link->exists = true;
			}
			//empty anchor target ?
			elseif(false !== strpos($url, '#'))
			{
				$id = substr($url,1);
				$doc = new \DOMDocument();
				$doc->loadHTMLFile($resource_path);
				$node = $doc->getElementById($id);
				if(is_null($node))
				{
					$link->error = "Une ancre a été détectée mais la cible n'a pas été trouvée dans le document";
				}
				else{
					$link->exists = true;
				}
			}
			//javascript:void(0) | onclick=my_function() ?
			//@todo naive check : preg_match ?
			elseif(false !== strpos($url,'(') && false !== strpos($url, ')'))
			{
				$link->error = "Le lien semble contenir une appel à une fonction javascript";
			}
			else
			{

				if(false !== strpos($url, 'http') )
				{
					$curl = $this->getCurl();
					$curl->get($url)->execute(array('link'=>$link));	

				}else 
				{
					//on prends le "resource->path" ( var/www/leaflet/index.html )
					//on substitue la référence au fichier par la valeur de l'attribut
					$chunks = explode('/', $resource_path);
					array_pop($chunks);
					$chunks[] = $url; //substr($url,1);
					$target = implode('/', $chunks);

					if(@file_get_contents($target))
					{
						$link->exists = true;
					}		
				}

			}

			$link->url = $url;
			$this->result->urls[$url] = $link;		
		
			if(false === $link->exists) $broken++;
			$total++;							
		}//foreach

		$this->result->link_count = $total;
		$this->result->broken = $broken;
		return $this->result;
	}


	protected function getCurl()
	{
		if(empty($this->curl)){

			$curl_options = array(
				CURLOPT_FAILONERROR => true,
				CURLOPT_NOBODY => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_FOLLOWLOCATION => false
				)
			;

			$this->curl = new \RollingCurl\RollingCurl();

			$this->curl
			->setSimultaneousLimit(10)
			->setOptions($curl_options)
			->setCallback(function(\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl, $options) {
				
				$link  = $options['link'];
				$error = $request->getResponseError();
				$infos = $request->getResponseInfo();
				$http_code = (int) $infos['http_code'];
		
				$link->url = $infos['url'];
				//@todo: prendr en compte certains code HTTP
				if(empty($error) 
					&& ($http_code >= 200 && $http_code < 400))
				{
					$link->exists = true;
				}
				else
				{
					$link->error = $error;
				}
				
				$link->url = $infos['url'];

				$this->result->urls[$link->url] = $link; 
				
			});

		}

		return $this->curl;
	}

	public function extractUrls($path)
	{
		$urls = array();
	
		$doc = new \DOMDocument();

		if(@$doc->loadHTMLFile($path))
		{
			
			$nodeList = $doc->getElementsByTagName('a');

			if($nodeList->length > 0)
			{
				foreach($nodeList as $node)
				{
					$urls[] = $node->getAttribute('href');
				}
			}

		}

		return $urls;

	}
}