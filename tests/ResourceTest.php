<?php


class ResourceTest extends \PHPUnit_Framework_TestCase
{
	public function testInit()
	{	
		
		$resource = new \Mudi\Resource(__DIR__ . "/Resources/demo.html");
		$this->assertTrue($resource instanceof \Mudi\Resource);

		return $resource;
	}

	/**
	 * @depends testInit
	 */
	public function testCheckHtmlFile($resource) {


		$this->assertTrue($resource->isFile);
		$this->assertTrue($resource->isHtml);
		$this->assertTrue($resource->ext === "html");
		$this->assertTrue($resource->isDir === false);
		$this->assertTrue($resource->isArchive === false);

	}

	public function testCheckDirectory() {

		$resource = new \Mudi\Resource(__DIR__ . '/Resources/');

		$this->assertTrue($resource->isDir);
		$this->assertTrue(empty($resource->ext));
		$this->assertTrue($resource->isHtml === false);
		$this->assertTrue($resource->isArchive === false);

	}


	public function testCheckArchive() {

		$resource = new \Mudi\Resource(__DIR__ . '/Resources/leaflet.zip');

		$this->assertTrue($resource->isArchive);
		$this->assertTrue($resource->isZip);
		$this->assertTrue($resource->ext === "zip");
		$this->assertTrue($resource->isHtml === false);
		$this->assertTrue($resource->isDir === false);

	}



}