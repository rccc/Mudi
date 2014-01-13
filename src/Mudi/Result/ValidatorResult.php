<?php

namespace Mudi\Result;

class ValidatorResult extends \Mudi\Result\MudiResult
{

	public $status 			= "";
	public $isValid			= false;
	public $error_count		= 0;
	public $warning_count  	= 0;
	public $messages   		= array();
	public $encoding 		= "";

	public function __construct()
	{
	}
}