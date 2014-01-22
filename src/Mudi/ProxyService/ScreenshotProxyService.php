<?php

namespace Mudi\ProxyService;

class ScreenshotProxyService extends \Mudi\ProxyService\ProxyService
{
	public function __construct($args = array())
	{
		$this->resource  = empty($args['resource']) ? new \Mudi\Resource($args['resource_name']) : $args['resource'];
		$this->service 	    = new \Mudi\Service\ScreenshotService(); 
		$this->results   	= new \Mudi\Collection\OutputCollection(); 
		$this->output_dir 	= $args['output_dir'];

		var_dump('???', $this->output_dir);
	}

	public function execute()
	{
		$files = $this->resource->getFiles('*.html');

		foreach($files as $file)
		{
			$this->results->add($file->getFileName(), $this->service->capture($file->getPathName(), $this->output_dir));			
		}

		return $this->results;

	}
	
}