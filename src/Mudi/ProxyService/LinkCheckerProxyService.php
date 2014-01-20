<?php

namespace Mudi\ProxyService;

class LinkCheckerProxyService extends \Mudi\ProxyService\ProxyService
{
	public function __construct($args = array())
	{

		$this->resource  = empty($args['resource']) ? new \Mudi\Resource($args['resource_name']) : $args['resource'];
		$this->service 	 = new \Mudi\Service\Link\LinkCheckerService(); 
		$this->results   = new \Mudi\Collection\OutputCollection(); 
		$this->method 	 = 'checkDocument'; //service:methode
		$this->arg_type  = 'path'; //path ou content
		$this->file_extension = '*.html';

	}
	
}