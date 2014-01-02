<?php

use  Mudi\Service\Validator\W3CMarkupValidatorService;

class W3CMarkupValidatorTest extends \PHPUnit_Framework_TestCase
{
	public function testInit()
	{	
		$validator = new W3CMarkupValidatorService();
		$this->assertTrue($validator instanceof \Mudi\Service\Validator\W3CMarkupValidatorService);
	}


	public function testInvalidFile()
	{	
		$validator = new W3CMarkupValidatorService();

		$file = file_get_contents(RESOURCES_PATH . 'demo.html');

		$result = $validator->validate($file);

		$this->assertTrue(!$result->isValid);
		$this->assertTrue($result->status === "Invalid");
		$this->assertTrue( (int) $result->errors === 1);
	}


	public function testValidFile()
	{	
		$validator = new W3CMarkupValidatorService();
		$file = file_get_contents(RESOURCES_PATH . 'valid-no-link.html');
		$result = $validator->validate($file);
		$this->assertTrue($result->isValid);
		$this->assertTrue($result->status === "Valid");
		$this->assertTrue(empty($result->errors));
	}

}