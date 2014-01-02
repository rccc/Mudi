<?php

namespace Mudi\ProxyService;

class LinkCheckerProxyService extends \Mudi\ProxyService\ProxyService
{
	public function __construct($name)
	{
		$this->resource  = new \Mudi\Resource($name);
		$this->service 	 = new \Mudi\Service\Link\LinkCheckerService(); 
		$this->results   = new \Mudi\Collection\OutputCollection(); 
		$this->method 	 = 'checkDocument'; //service:methode
		$this->arg_type  = 'path'; //path ou content
	}
	
}