<?php

namespace Mudi\ProxyService;

class CssUsageProxyService extends  \Mudi\ProxyService\ProxyService
{
	public function __construct($name = "", \Mudi\Resource $resource = null)
	{
		$this->resource  = empty($resource) ? new \Mudi\Resource($name) : $resource;
		$this->service 	 = new \Mudi\Service\CssUsageService(); 
		$this->results   = new \Mudi\Collection\OutputCollection(); 
		$this->method 	 = 'getUsage'; 
		$this->arg_type  = 'path'; 		//path ou content
		$this->file_extension = '*.css';

	}	
}