<?php

namespace Mudi\ProxyService;

class TidyProxyService extends \Mudi\ProxyService\ProxyService
{

	public function __construct($options = array())
	{
		$this->options = $options;
		$this->resource  = empty($this->options['resource']) ? new \Mudi\Resource($this->options['resource_name']) : $this->options['resource'];
		$this->service 	 = new \Mudi\Service\Validator\TidyMarkupValidatorService(); 
		$this->results   = new \Mudi\Collection\OutputCollection(); 
		$this->method 	 = 'validateFile'; //service
		$this->arg_type  = 'path'; //path ou content
		$this->file_extension = '*.html';

	}
	
}