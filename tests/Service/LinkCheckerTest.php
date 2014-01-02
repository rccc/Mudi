<?php

use Mudi\Service\Link\LinkCheckerService;

class LinkCheckerTest extends \PHPUnit_Framework_TestCase
{
	public function testInit()
	{	
		$c = new LinkCheckerService();
		$this->assertTrue($c instanceof \Mudi\Service\Link\LinkCheckerService);
	}


	public function testExtractUrlsNonEmpty()
	{
		$file_path 	=  RESOURCES_PATH . 'demo.html';
		$c 			= new LinkCheckerService();
		$urls  		= $c->extractUrls($file_path); 		

		$this->assertTrue(!empty($urls));
	}

	public function testExtractUrlsEmpty()
	{
		$file_path 	=  RESOURCES_PATH . 'valid-no-link.html';
		$c 			= new LinkCheckerService();
		$urls  		= $c->extractUrls($file_path); 		
		
		$this->assertTrue(empty($urls));
	}

	public function testCheckDocumentEmpty()
	{
		$file_path 	=  RESOURCES_PATH . 'valid-no-link.html';
		$c 			= new LinkCheckerService();
		$urls  		= $c->extractUrls($file_path); 		
		$results 	= $c->checkDocument($file_path);

		$this->assertTrue(empty($urls));
		$this->assertTrue(!empty($results->errors));

	}

	public function testCheckUrls()
	{	
		$urls = array(
			'http://labomedia.net',
			'http://dev.null',
			'',
			'#test',
			'#second-test',
			'onclick="my_function(args)"',
			'demo-2.html'
		);

		$c = new LinkCheckerService();

		$results = $c->checkUrls($urls, RESOURCES_PATH . 'demo.html');

		$this->assertTrue(!empty($results));

		foreach(array_keys($results->urls) as $key)
		{	
			static $i = 0;
			$this->assertTrue($key === $urls[$i]);
			$this->assertTrue($results->urls[$key] instanceof \Mudi\Service\Link\Link);
			$i++;
		}

		$this->assertTrue( $results->urls['http://labomedia.net']->exists);
		$this->assertTrue(!$results->urls['http://dev.null']->exists);
		//$this->assertTrue(!$results->urls['']->exists);
		$this->assertTrue(!$results->urls['#test']->exists);
		$this->assertTrue( $results->urls['#second-test']->exists);
		$this->assertTrue(!$results->urls['onclick="my_function(args)"']->exists);
		$this->assertTrue($results->urls['demo-2.html']->exists);

	}


}