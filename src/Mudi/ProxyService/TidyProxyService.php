<?php

namespace Mudi\ProxyService;

class TidyProxyService extends \Mudi\ProxyService\ProxyService
{

	public function __construct($args = array())
	{
		$this->resource  = empty($args['resource']) ? new \Mudi\Resource($args['resource_name']) : $args['resource'];
		$this->service 	 = new \Mudi\Service\Validator\TidyMarkupValidatorService(); 
		$this->results   = new \Mudi\Collection\OutputCollection(); 
		$this->method 	 = 'validateFile'; //service
		$this->arg_type  = 'path'; //path ou content
		$this->file_extension = '*.html';

	}
	
}