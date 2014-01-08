<?php

namespace Mudi\Service;

class TagUsageService 
{
	public function __construct()
	{

		$this->result = new \Mudi\Result\TagUsageResult();
		$this->name = "tag_usage";
	}

	public function getUsage($path)
	{
		$this->getStats($path);
		$this->result->medias			= $this->getMedias();
		$this->result->common_semantics = $this->getCommonSemantics();
		$this->result->headings 		= $this->getHeadings();
		return $this->result;
	}

	public function getStats($path)
	{
		libxml_use_internal_errors(true);

		$doc = new \DOMDocument();

		if(!$doc->loadHTMLFile($path))
		{
			foreach (libxml_get_errors() as $error) {
				$result->errors[] = $errors;
			}

			libxml_clear_errors();
		}
		else
		{
			$tagList = $doc->getElementsByTagName('*');
			if($tagList->length == 0)
			{
				$result->errors[] = "Aucun balises n'a été trouvée";
			}
			else{

				$count_list = array();
				foreach($tagList as $tag)
				{
					$tagName = $tag->tagName;
					if(in_array($tagName, array_keys($count_list))){
						$count_list[$tagName]++;
					}
					else{
						$count_list[$tagName] = 1;
					}
				}
			}

		}
		ksort($count_list);
		$this->result->stats = $count_list;
		return $this->result;

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
		$headings = array('h1','h2', 'h3', 'h4', 'h5', 'h6', 'hgroup');
		return  array_values( array_intersect( array_keys($this->result->stats), $headings) );
	}
}