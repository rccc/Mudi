<?php

namespace Mudi\ProxyService;

class LinkCheckerProxyService extends \Mudi\ProxyService\ProxyService
{
	public function __construct($name = "", \Mudi\Resource $resource = null)
	{

		$this->resource  = empty($resource) ? new \Mudi\Resource($name) : $resource;
		$this->service 	 = new \Mudi\Service\Link\LinkCheckerService(); 
		$this->results   = new \Mudi\Collection\OutputCollection(); 
		$this->method 	 = 'checkDocument'; //service:methode
		$this->arg_type  = 'path'; //path ou content
	}
	
}