<?php

namespace Mudi\Result;

class CssUsageResult extends \Mudi\Result\MudiResult
{
	public $media_query_count 	= 0;
	public $css3_count 			= 0;
	public $css3_no_vendor		= 0;
	public $css_rules			= array();
	public $css3_rules			= array();
	public $media_queries		= array();

	public function __construct()
	{

	}
}