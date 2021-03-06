<?php

namespace Mudi\Service;

class TagUsageService 
{

	public 		$name;
	public 		$result;
	protected 	$doc;
	protected 	$tagList;
	protected 	$file_path;

	public function __construct($file_path = '')
	{
		$this->file_path = $file_path;
		$this->result = new \Mudi\Result\TagUsageResult();
		$this->name = "tag_usage";
		$this->doc = new \DOMDocument();

	}

	public function getUsage($file_path = '')
	{
		if(!empty($file_path))
		{
			$this->file_path = $file_path;
		}

		$this->parse_file();

		$this->result->stats 			= $this->getStats();
		$this->result->medias			= $this->getMedias();
		$this->result->common_semantics = $this->getCommonSemantics();
		$this->result->headings 		= $this->getHeadings();
		$this->result->class_attr	 	= $this->countClassAttr();

		return $this->result;
	}

	protected function parse_file()
	{
		libxml_use_internal_errors(true);

		if(empty($this->tagList))
		{
			if(!$this->doc->loadHTMLFile($this->file_path))
			{
				foreach (libxml_get_errors() as $error) {
					$this->result->errors[] = $error;
				}

				libxml_clear_errors();
			}
			else{
				$this->tagList = $this->doc->getElementsByTagName('*');
				if($this->tagList->length == 0)
				{
					$this->result->errors[] = "Aucun balises n'a été trouvée";
				}
			}
		}		
	}

	protected function getStats()
	{

		$count_list = array();
		foreach($this->tagList as $tag)
		{
			$tagName = $tag->tagName;
			if(in_array($tagName, array_keys($count_list))){
				$count_list[$tagName]++;
			}
			else{
				$count_list[$tagName] = 1;
			}
		}

		ksort($count_list);
		return $count_list;

	}

	protected function getMedias()
	{
		$medias = array('audio', 'video', 'source', 'embed', 'track');

		return array_values( array_intersect( array_keys($this->result->stats), $medias) );
	}


	protected function getSemantics()
	{
		$semantics = array('article','aside', 'bdi', 'command', 'details', 'dialog', 'figure', 'figcaption', 'footer', 'header', 'mark', 'meter', 'nav', 'ruby', 'rt', 'rp', 'section', 'time', 'wbr');

		return  array_intersect( array_keys($this->result->stats), $semantics);
	}

	protected function getCommonSemantics()
	{
		$semantics = array('article','aside', 'footer', 'header',  'nav', 'section');

		return  array_values( array_intersect( array_keys($this->result->stats), $semantics) );
	}

	protected function getHeadings()
	{
		$headings = array('h1','h2','hgroup');
		return  array_values( array_intersect( array_keys($this->result->stats), $headings) );
	}

	protected function countClassAttr()
	{
		foreach($this->tagList as $tag)
		{
			$attr = $tag->getAttribute('class');

			if(!empty($attr)){
				$this->result->class_attr++;
			}
		}
	}

}