<?php

namespace Mudi\ProxyService;

class W3CMarkupValidatorProxyService extends \Mudi\ProxyService\ProxyService
{
	protected $resource;
	protected $validator;
	protected $results;

	public function __construct($options = array())
	{
		$opts['service_url'] = !empty($options['service_url']) ? $options['service_url'] : array();
		$this->resource  = !empty($options['resource']) ? $options['resource'] : new \Mudi\Resource($options['resource_name']);
        $this->service   = new \Mudi\Service\Validator\W3CMarkupValidatorService($opts); 
        $this->results   = new \Mudi\Collection\OutputCollection(); 
		$this->method 	 = 'validate'; //service
		$this->arg_type  = 'content'; //path ou content
		$this->file_extension = "*.html";
	}

}