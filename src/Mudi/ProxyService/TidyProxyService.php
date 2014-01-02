<?php

namespace Mudi\ProxyService;

class TidyProxyService extends \Mudi\ProxyService\ProxyService
{

	public function __construct($name)
	{
		$this->resource  = new \Mudi\Resource($name);
		$this->service 	 = new \Mudi\Service\Validator\TidyMarkupValidatorService(); 
		$this->results   = new \Mudi\Collection\OutputCollection(); 
		$this->method 	 = 'validateFile'; //service
		$this->arg_type  = 'path'; //path ou content
	}
	
}