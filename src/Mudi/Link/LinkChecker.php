<?php

namespace Mudi\Link;

/**
 * vérifie la validité des liens trouvés dans un fichier 'HTML'
 */

class LinkChecker
{

	protected $results;

	public function __construct()
	{
		$this->results = array();
	}

	public function check(Array $urls, $resource_path)
	{
		$this->results = array();
		
		foreach($urls as $url)
		{			

			$link = new \Mudi\Link\Link();
			$link->raw_url = $url;

			//empty href attribute ?
			if(empty($url)){
				$link->error = 'Un lien dont la valeur de l\'attribut "href" est vide a été trouvé dans le document';
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
					$chunks[] = substr($url,1);
					$url = implode('/', $chunks);

					if(file_get_contents($url))
					{
						$link->exists = true;
					}		
				}

			}

			$link->url = $url;
			$this->results[$url] = $link;						

		}//foreach

		return $this->results;
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

				$this->results[$link->url] = $link; 
				
			});

		}

		return $this->curl;

	}}