<?php

namespace Mudi\Service;

use Symfony\Component\Process\Process;


class ScreenshotService
{
	protected $filename;
	protected $output_dir;

	public function __construct($filename, $output_dir)
	{
		$this->filename =  $filename;
		$this->output_dir = $output_dir;

		
	}

	public function capture()
	{
		$cmd = sprintf("casperjs %s/casperscreen.coffee '%s' %s", RESOURCES_PATH, $this->filename, $this->output_dir);
		
		$process = new Process($cmd);
		$process->setTimeout(3600);
		$process->run();
		if (!$process->isSuccessful()) {
		    throw new \RuntimeException($process->getErrorOutput());
		}
		else{
			$result = new \stdClass();
			$result->message = basename($process->getOutput());
			return $result;
		}

	}

}