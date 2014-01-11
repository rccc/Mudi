<?php

namespace Mudi\Service;

use Symfony\Component\Process\Process;


class ScreenshotService
{
	protected $filename;
	protected $output_dir;

	public function __construct()
	{	
		$this->name = 'screenshot';
	}

	public function capture($filename, $output_dir)
	{
		$cmd = sprintf("casperjs %s/casperscreen.coffee '%s' %s", RESOURCES_PATH, $filename, $output_dir);
		
		$process = new Process($cmd);
		$process->setTimeout(3600);
		$process->run();
		if (!$process->isSuccessful()) {
		    throw new \RuntimeException($process->getErrorOutput());
		}
		else{
			$result = new \stdClass();
			$result->message = basename($process->getOutput());
			//echo $result->message;
			return $result;
		}

	}

}