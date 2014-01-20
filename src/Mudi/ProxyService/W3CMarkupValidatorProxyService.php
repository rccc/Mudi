<?php

namespace Mudi\ProxyService;

class W3CMarkupValidatorProxyService extends \Mudi\ProxyService\ProxyService
{
	protected $resource;
	protected $validator;
	protected $results;

	public function __construct($args = array())
	{
		$this->resource  = empty($args['resource']) ? new \Mudi\Resource($args['resource_name']) : $args['resource'];
        $this->service   = new \Mudi\Service\Validator\W3CMarkupValidatorService(); 
        $this->results   = new \Mudi\Collection\OutputCollection(); 
		$this->method 	 = 'validate'; //service
		$this->arg_type  = 'content'; //path ou content
		$this->file_extension = "*.html";
	}

}