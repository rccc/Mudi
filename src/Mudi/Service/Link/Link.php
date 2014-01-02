<?php

namespace Mudi\Service\Link;

class Link
{
	/**
	 * $isRemote 
	 * @var boolean
	 */
	public $isRemote;
	
	/**
	 * [$exists description]
	 * @var boolean
	 */
	public $exists;
	
	/**
	 * $raw_url raw href attribute value 
	 * @var string
	 */
	public $raw_url;

	/**
	 * $url cleaned href attribute values
	 * @var string
	 */
	public $url;

	/**
	 * [$error description]
	 * @var string
	 */
	public $error;


	public function __construct()
	{
		$this->isRemote = false;
		$this->exists   = false;
		$this->raw_url  = '';
		$this->url      = '';
		$this->error    = '';
	}

}