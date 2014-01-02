<?php

use  Mudi\Service\Validator\TidyMarkupValidatorService;

class TidyMarkupValidatorTest extends \PHPUnit_Framework_TestCase
{
	public function testInit()
	{	
		$validator = new TidyMarkupValidatorService();
		$this->assertTrue($validator instanceof \Mudi\Service\Validator\TidyMarkupValidatorService);
	}


	public function testInvalidFile()
	{	
		$validator = new TidyMarkupValidatorService();

		//$file = file_get_contents();

		$result = $validator->validateFile(RESOURCES_PATH. 'demo.html');
		$this->assertTrue($result->status === 1);
	}


	public function testValidFile()
	{	
		$validator = new TidyMarkupValidatorService();
		//$file = file_get_contents(RESOURCES_PATH . 'valid-no-link.html');
		$result = $validator->validateFile(RESOURCES_PATH . 'valid-no-link.html');

		$this->assertTrue($result->status === 1);
	}

}