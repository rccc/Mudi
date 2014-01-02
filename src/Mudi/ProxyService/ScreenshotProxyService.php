<?php

namespace Mudi\ProxyService;

class ScreenshotProxyService extends \Mudi\ProxyService\ProxyService
{
	public function __construct($name, $output_dir)
	{
		$this->resource  	= new \Mudi\Resource($name);
		$this->results   	= new \Mudi\Collection\OutputCollection(); 
		$this->output_dir 	= $output_dir;
	}

	public function execute()
	{
		$files = $this->resource->getFiles('*.html');

		foreach($files as $file)
		{
			$this->service 	 = new \Mudi\Service\ScreenshotService($file->getPathName(), $this->output_dir); 
			$this->results->add($file->getFileName(), $this->service->capture());			
		}

		return $this->results;

	}
	
}