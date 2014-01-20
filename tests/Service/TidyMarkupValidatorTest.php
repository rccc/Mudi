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
		$result = $validator->validateFile(RESOURCES_PATH. 'demo.html');
		$this->assertTrue($result->count_errors === 2);
	}


	public function testValidFile()
	{	
		$validator = new TidyMarkupValidatorService();
		$result = $validator->validateFile(RESOURCES_PATH . 'valid-no-link.html');
		$this->assertTrue($result->count_error === 0);
	}

}