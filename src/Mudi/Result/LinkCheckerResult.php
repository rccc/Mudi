<?php

namespace Mudi\Result;

class LinkCheckerResult extends \Mudi\Result\MudiResult
{

	public $urls 		= array();
	public $broken 		= 0;
	public $errors 		= array();
	public $link_count 	= 0;

	public function __construct()
	{
	}
}