<?php

namespace Mudi\ProxyService;

class W3CCssValidatorProxyService extends \Mudi\ProxyService\ProxyService
{
	public function __construct($name = "", \Mudi\Resource $resource = null)
	{
		$this->resource  = empty($resource) ? new \Mudi\Resource($name) : $resource;
		$this->service 	 = new \Mudi\Service\Validator\W3CCssValidatorService(); 
		$this->results   = new \Mudi\Collection\OutputCollection(); 
		$this->method 	 = 'validate'; //service
		$this->arg_type  = 'content'; //path ou content
	}
	
}