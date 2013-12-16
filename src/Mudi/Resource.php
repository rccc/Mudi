<?php

namespace Mudi;

class Resource
{
	public $isFile		= false;
	public $isHtml		= false;
	public $isDir		= false;
	public $isArchive	= false;
	public $isZip		= false;
	public $ext			= "";
	public $results		= array();
	public $name 		= "";
	public $path 		= "";


	public function getPathFromName($name)
	{
		return stream_resolve_include_path($name);
	}

}