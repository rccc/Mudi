<?php

use Mudi\Service\TagUsageService;

class TagUsageTest extends \PHPUnit_Framework_TestCase
{
	public function testInit()
	{	
		$file_path 	=  RESOURCES_PATH . 'demo.html';
		$service = new TagUsageService($file_path);
		$this->assertTrue($service instanceof \Mudi\Service\TagUsageService);
	}


	public function testGetStats()
	{
		$file_path 	=  RESOURCES_PATH . 'demo.html';

		$service 	= new TagUsageService($file_path);
		$result  	= $service->getUsage(); 		

var_dump($result);

		$this->assertTrue( !empty($result->stats) );
		$this->assertTrue( count($result->stats) > 0 );

	}

	public function testgetUsage()
	{
		$file_path 	=  RESOURCES_PATH . 'demo.html';

		$service 	= new TagUsageService($file_path);
		$result  	= $service->getUsage(); 

		$this->assertTrue( $result instanceof \Mudi\Result\TagUsageResult );		
		$this->assertTrue( count($result->stats) > 0 );
	}

}