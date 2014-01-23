<?php

namespace Mudi\ProxyService;

class W3CCssValidatorProxyService extends \Mudi\ProxyService\ProxyService
{
	public function __construct($args = array())
	{
		$opts['service_url'] 	= !empty($args['service_url']) ? $args['service_url'] : array();
		$this->resource  = !empty($args['resource']) ? $args['resource'] : new \Mudi\Resource($args['resource_name']);
		$this->service 	 = new \Mudi\Service\Validator\W3CCssValidatorService($opts); 
		$this->results   = new \Mudi\Collection\OutputCollection(); 
		$this->method 	 = 'validate'; //service
		$this->arg_type  = 'content'; //path ou content
		$this->file_extension = '*.css';
	}
	
}