<?php

namespace Mudi\ProxyService;

class TidyProxyService extends \Mudi\ProxyService\ProxyService
{

	public function __construct($name = "", \Mudi\Resource $resource = null)
	{
		$this->resource  = empty($resource) ? new \Mudi\Resource($name) : $resource;
		$this->service 	 = new \Mudi\Service\Validator\TidyMarkupValidatorService(); 
		$this->results   = new \Mudi\Collection\OutputCollection(); 
		$this->method 	 = 'validateFile'; //service
		$this->arg_type  = 'path'; //path ou content
		$this->file_extension = '*.html';

	}
	
}