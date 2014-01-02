<?php

namespace Mudi\ProxyService;

class TagUsageProxyService extends \Mudi\ProxyService\ProxyService
{

	public function __construct($name)
	{
		$this->resource  = new \Mudi\Resource($name);
		$this->service 	 = new \Mudi\Service\TagUsageService(); 
		$this->results   = new \Mudi\Collection\OutputCollection(); 
		$this->method 	 = 'getStats'; //service
		$this->arg_type  = 'path'; //path ou content
	}
	
}