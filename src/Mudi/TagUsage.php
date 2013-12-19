<?php

namespace Mudi;

class TagUsage
{
	protected $results;

	public function __constuct()
	{
		$this->results = array();
	}

	public function getUsageStats( $content)
	{
		libxml_use_internal_errors(true);

		$doc = new \DOMDocument();

		if(!$doc->loadHTML($content))
		{
			foreach (libxml_get_errors() as $error) {
				$this->results[] = $errors;
			}

			libxml_clear_errors();
		}
		else
		{
			$tagList = $doc->getElementsByTagName('*');
			if($tagList->length == 0)
			{
				$this->results = "Aucun balises n'a été trouvée";
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

			$this->results =  $count_list;

		}

		return $this->results;

	}
}