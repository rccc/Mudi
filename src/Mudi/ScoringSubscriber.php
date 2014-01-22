<?php

namespace Mudi;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class ScoringSubscriber implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'service.done'     => array('onServiceDone', 0),
			);
	}

	public function onServiceDone(\Mudi\Event $event)
	{
		$service_name 	= $event->getServiceName();
		$resource_name 	= $event->getResourceName();
		$results 		= $event->getResults();
		$method 		= $service_name . '_scoring';
		
		if(method_exists($this, $method))
		{
			$this->$method($service_name, $resource_name, $results);
		}

	}

	protected function link_checker_scoring($service_name, $resource_name, $results)
	{
		//var_dump('link_checker_scoring');
		$broken = 0;
		$nb_doc = 0;
		$value  = 0;
		
		foreach($results as $document_name => $result)
		{
			if(!empty($result->urls))
			{
				foreach($result->urls as $url => $link)
				{	
					if(false === $link->exists)
					{	
						$broken++;
						$value += 0.5;
					}
				}
			}
			$nb_doc++;
		}

		if($broken > 0)
		{	
			$value = round($value / $nb_doc,1);
			$this->decrementScore($resource_name, $value);
			$this->addScoringMessage($resource_name, $service_name, $document_name, sprintf("%d lien(s) cassé(s) dans (%d) documents", $broken, $nb_doc));
		}
	}

	protected function w3c_markup_validator_scoring($service_name, $resource_name, $results)
	{
		//var_dump('w3c_markup_validator_scoring');

		foreach ($results as $document_name => $result) 
		{
			if(!empty($result->errors))
			{
				$errors = (int) $result->errors;
				if($errors > 5)
				{
					$value = 5;
				}
				else
				{
					$value = $errors ;
				}
				$this->decrementScore($resource_name, $value);
				$this->addScoringMessage($resource_name, $service_name, $document_name, "$errors erreur(s)");
			}
		}
	}

	protected function tidy_validator_scoring($service_name, $resource_name, $results)
	{
		//var_dump("tidy_validator_scoring");

		$nb_doc 	= 0;
		$invalid 	= 0;
		$value 		= 0;

		foreach($results as $document_name => $result)
		{	
			static $nb_doc = 0;
			if($result->count_errors > 0) $invalid++;

			if($result->count_errors > 6)
			{
				$value += 6;
			}
			elseif($result->count_errors > 0 && $result->count_errors <= 6)
			{
				$value += $result->count_errors;
			}

			$nb_doc++;
		}

		$value = round($value/$nb_doc,1);
		$this->decrementScore($resource_name, $value);
		$this->addScoringMessage($resource_name, $service_name, $document_name, "$invalid document(s) non valide(s)");
	}

	protected function tag_usage_scoring($service_name, $resource_name, $results)
	{
		//var_dump('tag_usage_scoring');

		$wanted_semantics = array('header', 'footer', 'article', 'section','nav','aside');
		$wanted_headings  = array('h1', 'h2', 'hgroup');

		$value 	= 0; //valeur à déduire
		$nb_doc = 0; 
		$no_semantics= 0;
		$no_headings = 0;
		$with_style = 0;

		foreach($results as $document_name => $result)
		{	
			//diff_s retourne les balises sémantiques attendues qui ne sont pas présentes dans le document
			$diff_s = array_diff($wanted_semantics, $result->common_semantics); 
			if(!empty($diff_s))
			{
				//on retire un demi-point pour chaque balise non utilisée
				$value += count($diff_s)* 0.5;
				$no_semantics++;
			}

			//diff_h retourne les balises heading attendues qui ne sont pas présentes dans le document
			$diff_h = array_diff($wanted_headings, $result->headings); 
			if(!empty($diff_h))
			{
				//on retire un demi-point pour chaque balise non utilisée
				$value += count($diff_h)* 0.5;
				$no_headings++;
			}

			//test présence balise "style" - on retire 3 points
			if(in_array('style', array_keys($result->stats)))
			{
				$this->decrementScore($resource_name, 3);
				$with_style++;
			}

			//test si utilisation de l'attribut "class"
			if($result->class_attr === 0)
			{
				$this->decrementScore($resource_name, 3);
				$this->addScoringMessage($resource_name, $service_name, $document_name, "L'attribut \"class\" n'est pas utilisé");
			}

			$nb_doc++;
		}


		//on divise par le nombre de document
		$value = round($value / $nb_doc,1);
		$this->decrementScore($resource_name, $value);

		if($no_semantics > 0)
			$this->addScoringMessage($resource_name, $service_name, $document_name, "$no_semantics document(s) avec balises sémantiques manquantes");
		
		if($no_headings > 0)
			$this->addScoringMessage($resource_name, $service_name, $document_name, "$no_headings document(s) avec balises d'en-tête manquantes");
		
		if($with_style > 0)		
			$this->addScoringMessage($resource_name, $service_name, $document_name, "$with_style document(s) avec balise(s) style détectée(s)");				

	}


	protected function w3c_css_validator_scoring($service_name, $resource_name, $results)
	{
		$value = 0;
		$nb_doc = 0;
		$invalid = 0;

		foreach($results as $document_name => $result)
		{
			if($result->error_count > 0)
			{
				$value += 2;
				$invalid++;
			}

			$nb_doc++;
		}

		$value = round($value / $nb_doc,1);
		$this->decrementScore($value);
		$this->addScoringMessage($resource_name, $service_name, $document_name, sprintf('%d fichiers css invalides sur %d', $invalid, $nb_doc));
	}

	protected function css_usage_scoring($service_name, $resource_name, $results)
	{
		$value 	= 0;
		$nb_doc = 0;
		$css3  	= 0;
		$media_queries = 0;
		$no_vendor     = 0;

		foreach($results as $document_name => $result)
		{
			if(empty($result->css3_rules))
			{
				$value += 2;
			}
			else{
				$css3++;
				$value -= count($result->css3_rules) * 0.5;

				if($result->css3_no_vendor > 0)
				{
					$no_vendor++;
					$value += count($result->css3_no_vendor) * 1;
				}

			}

			if(empty($result->media_queries))
			{
				$value += 2;
			}
			else{
				$media_queries++;
				$value -= count($result->media_queries) * 1;
			}

			$nb_doc++;
		}

		$value = round($value / $nb_doc,1);
		$this->decrementScore($value);

		if($css3 === 0)
			$this->addScoringMessage($resource_name, $service_name, $document_name, "pas de règles CSS3 dans le document");
	
		if($media_queries === 0)
			$this->addScoringMessage($resource_name, $service_name, $document_name, "pas de media queries dans le document");
	
		if($no_vendor > 0)
			$this->addScoringMessage($resource_name, $service_name, $document_name, "'Vendor prefix' manquants");
	

	}

	protected function decrementScore($resource_name, $value = 1)
	{	
		$key = $resource_name . "_score";

		\Mudi\Registry::set( $key,   ( \Mudi\Registry::get($key) - $value ) );

	}	

	protected function addScoringMessage($resource_name, $service_name, $document_name, $message)
	{
		$key = $resource_name . "_scoring_messages";
		if(!\Mudi\Registry::has($key))
		{
			\Mudi\Registry::set($key, array());
		}
		$messages = \Mudi\Registry::get($key);
		$messages[] = implode(' - ', func_get_args());
		\Mudi\Registry::set($key, $messages);
	}
}