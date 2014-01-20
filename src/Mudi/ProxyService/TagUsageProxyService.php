<?php

namespace Mudi\ProxyService;

class TagUsageProxyService extends \Mudi\ProxyService\ProxyService
{
	public function __construct($args = array())
	{
		$this->resource  = empty($args['resource']) ? new \Mudi\Resource($args['resource_name']) : $args['resource'];
		$this->service 	 = new \Mudi\Service\TagUsageService($this->resource->path); 
		$this->results   = new \Mudi\Collection\OutputCollection(); 
		$this->method 	 = 'getUsage'; //service
		$this->arg_type  = 'path'; //path ou content
		$this->file_extension = '*.html';

	}
	
}