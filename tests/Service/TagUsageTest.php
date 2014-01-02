<?php

use Mudi\Service\TagUsageService;

class TagUsageTest extends \PHPUnit_Framework_TestCase
{
	public function testInit()
	{	
		$service = new TagUsageService();
		$this->assertTrue($service instanceof \Mudi\Service\TagUsageService);
	}


	public function testNonEmpty()
	{
		$file_path 	=  RESOURCES_PATH . 'demo.html';
		$service 	= new TagUsageService();
		$result  	= $service->getStats($file_path); 		

		$this->assertTrue( !empty($result->stats) );
		$this->assertTrue( count($result->stats) > 0 );

	}

}