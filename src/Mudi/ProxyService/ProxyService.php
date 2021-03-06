<?php

namespace Mudi\ProxyService;

use Symfony\Component\Finder\Finder;
use Neutron\TemporaryFilesystem\TemporaryFilesystem;

class ProxyService
{
	protected $file_extension;
	protected $options;
	protected $resource;
	protected $results;
	protected $service;

	public function execute()
	{
		if($this->resource->isFile && !$this->resource->isArchive)
		{
			$arg = $this->arg_type === 'path' ? $this->resource->path : file_get_contents($this->resource->path);
			$this->results->add($this->resource->name, call_user_func(array($this->service, $this->method), $arg));
		}
		else 
		{
			if($this->resource->isArchive)
			{
				if(empty($this->resource->archive_path))
				{
					$fs = TemporaryFilesystem::create();
					$path = $fs->createTemporaryDirectory();

					$zip = new \ZipArchive();
					if ($zip->open($this->resource->path) === TRUE) {
						$zip->extractTo($path);
						$zip->close();
					} 
				}
				else
				{
					$path = $this->resource->archive_path;
				}
			}
			else
			{
				$path = $this->resource->path;
			}

			$finder = new Finder();        
			$finder->files()->in($path)->name($this->file_extension);

			foreach ($finder as $file) 
			{  
				$arg = $this->arg_type === 'path' ? $file->getRealpath() : $file->getContents();
				$this->results->add( $file->getFileName(), call_user_func(array($this->service, $this->method), $arg) );
			}  

		}

		return $this->results;		
	}

	public function getService()
	{
		return $this->service;
	}

	public function getResource()
	{
		return $this->resource;
	}

	public function getResults()
	{
		return $this->results->all();
	}

	public function __set($name, $value)
	{
		$this->options[$name] = $value;
	}

	public function __get($name)
	{
		if(array_key_exists($name, $this->options))
		{
			return $this->options[$name];
		}
		return null;
	}

}
