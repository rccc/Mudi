<?php

namespace Mudi\Service;

class TagUsageService
{

	protected $results;

	public function __construct()
	{
		$this->results = array();
	}

	public function getStats($path)
	{
		libxml_use_internal_errors(true);

		$doc = new \DOMDocument();
		$result = new \stdClass();

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

			$result->stats =  $count_list;

		}

		return $result;

	}
}