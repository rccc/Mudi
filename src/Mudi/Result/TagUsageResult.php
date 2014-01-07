<?php

namespace Mudi\Result;

class TagUsageResult extends \Mudi\Result\MudiResult
{

	public $stats;
	public $count_medias;
	public $count_semantics;

	public function __construct()
	{
		$this->stats 			= array();
		$this->count_medias 	= 0;
		$this->count_semantics 	= 0;
	}
}