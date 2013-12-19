<?php


class LinkCheckerTest extends \PHPUnit_Framework_TestCase
{
	public function testInit()
	{	
		$c= new \Mudi\Link\LinkChecker();
		$this->assertTrue($c instanceof \Mudi\Link\LinkChecker);
	}


	public function testCheckHtmlFile()
	{	
		$urls = array('http://labomedia.net','http://dev.null','','#test','#second-test','onclick="my_function(args)"');

		$c = new \Mudi\Link\LinkChecker();

		$results = $c->check($urls, __DIR__ . '/Resources/demo.html');

		$this->assertTrue(!empty($results));

		foreach(array_keys($results) as $key)
		{	
			static $i = 0;
			$this->assertTrue($key === $urls[$i]);
			$this->assertTrue($results[$key] instanceof \Mudi\Link\Link);
			$i++;
		}

		$this->assertTrue( $results['http://labomedia.net']->exists);
		$this->assertTrue(!$results['http://dev.null']->exists);
		$this->assertTrue(!$results['#test']->exists);
		$this->assertTrue( $results['#second-test']->exists);
		$this->assertTrue(!$results['onclick="my_function(args)"']->exists);

	}


}