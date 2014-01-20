<?php

namespace Mudi\ProxyService;

class CssUsageProxyService extends  \Mudi\ProxyService\ProxyService
{
	public function __construct($args = array())
	{
		$this->resource  = empty($args['resource']) ? new \Mudi\Resource($args['resource_name']) : $args['resource'];
		$this->service 	 = new \Mudi\Service\CssUsageService(); 
		$this->results   = new \Mudi\Collection\OutputCollection(); 
		$this->method 	 = 'getUsage'; 
		$this->arg_type  = 'path'; 		//path ou content
		$this->file_extension = '*.css';

	}	
}