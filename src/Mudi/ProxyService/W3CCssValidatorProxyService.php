<?php

namespace Mudi\ProxyService;

class W3CCssValidatorProxyService extends \Mudi\ProxyService\ProxyService
{
	public function __construct($args = array())
	{
		$this->resource  = empty($args['resource']) ? new \Mudi\Resource($args['resource_name']) : $args['resource'];
		$this->service 	 = new \Mudi\Service\Validator\W3CCssValidatorService(); 
		$this->results   = new \Mudi\Collection\OutputCollection(); 
		$this->method 	 = 'validate'; //service
		$this->arg_type  = 'content'; //path ou content
		$this->file_extension = '*.css';
	}
	
}