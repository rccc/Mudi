<?php

namespace Mudi\ProxyService;

class ScreenshotProxyService extends \Mudi\ProxyService\ProxyService
{
	public function __construct($name = "", $output_dir, \Mudi\Resource $resource = null)
	{
		$this->resource     = empty($resource) ? new \Mudi\Resource($name) : $resource;
		$this->service 	    = new \Mudi\Service\ScreenshotService(); 
		$this->results   	= new \Mudi\Collection\OutputCollection(); 
		$this->output_dir 	= $output_dir;
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