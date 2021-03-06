<?php

namespace Mudi\Result;

class TagUsageResult extends \Mudi\Result\MudiResult
{

	public $stats;
	public $medias;
	public $semantics;
	public $common_semantics;
	public $headings;
	public $class_attr;

	public function __construct()
	{
		$this->stats 			= array();
		$this->medias 			= array();
		$this->semantics 		= array();
		$this->common_semantics = array();
		$this->headings 		= array();
		$this->class_attr 		= 0;
	}
}