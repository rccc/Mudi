<?php

namespace Mudi;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class ScoringSubscriber implements EventSubscriberInterface
{
	protected $config;
	protected $is_first;

	public function __construct($config)
	{
		$this->config = $config;
		$this->is_first = true;
	}

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

		if($this->is_first)
		{
			$this->count_file_scoring($event->getResource());
			$this->is_first = false;
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
						$value += $this->config['lien_non_valide'];
					}
				}
			}
			$nb_doc++;
		}

		if($broken > 0)
		{	
			$value = round($value / $nb_doc,1);
			$this->decrementScore($resource_name, $value);
			$this->addScoringMessage($resource_name, $service_name, '', sprintf("%d lien(s) cassé(s) dans (%d) documents", $broken, $nb_doc));
		}
		else
		{
			$this->addScoringMessage($resource_name, $service_name, '', sprintf("Aucun lien(s) cassé(s) (%d documents)", $nb_doc));

		}
		
		$this->addScoringMessage($resource_name, $service_name, '', sprintf("<b>scoring : %d </b>", $value));

	}

	protected function w3c_markup_validator_scoring($service_name, $resource_name, $results)
	{
		//var_dump('w3c_markup_validator_scoring');
		$doc_with_error = 0;
		$nb_doc = 0;
		$value = 0;

		foreach ($results as $document_name => $result) 
		{
			$nb_doc++;

			if(!empty($result->errors))
			{
				$doc_with_error++;
				$errors = (int) $result->errors;
				if($errors > $this->config['html_max_errors'])
				{
					$value += $this->config['html_max_errors'];
				}
				else
				{
					$value += $this->config['html_error'] * $errors ;
				}
			}
		}

		$value = round($value/$nb_doc,1);
		$this->decrementScore($resource_name, $value);
		$this->addScoringMessage($resource_name, $service_name, '', sprintf("%d document(s) contenant des erreurs sur %d document(s)", $doc_with_error, $nb_doc));
		$this->addScoringMessage($resource_name, $service_name, '', sprintf("<b>scoring : %d </b>", $value));

	}

	protected function tidy_validator_scoring($service_name, $resource_name, $results)
	{
		//var_dump("tidy_validator_scoring");

		$nb_doc 	= 0;
		$invalid 	= 0;
		$value 		= 0;
		$total_errors = 0;

		foreach($results as $document_name => $result)
		{	
			static $nb_doc = 0;
			if($result->count_errors > 0) $invalid++;

			if($result->count_errors > $this->config['tidy_max_errors'])
			{
				$value += $this->config['tidy_max_errors'];
			}
			elseif($result->count_errors > 0 && $result->count_errors <= $this->config['tidy_max_errors'])
			{
				$value += $this->config['tidy_error'] * $result->count_errors;
			}

			$total_errors += $result->count_errors;
			$nb_doc++;
		}

		$value = round($value/$nb_doc,1);
		$this->decrementScore($resource_name, $value);
		$this->addScoringMessage($resource_name, $service_name, '', sprintf("%d document(s) non valide(s) sur %d document(s), %d erreur(s) au total", $invalid, $nb_doc, $total_errors));
		$this->addScoringMessage($resource_name, $service_name, '', sprintf("<b>scoring : %d </b>", $value));

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
		$no_class_attr = 0;

		foreach($results as $document_name => $result)
		{	

			if(empty($result->common_semantics))
			{
				$value += $this->config['no_semantics'];
				$no_semantics++;

			}
			else
			{
				//diff_s retourne les balises sémantiques attendues qui ne sont pas présentes dans le document
				$diff_s = array_diff($wanted_semantics, $result->common_semantics); 
				if(!empty($diff_s))
				{
					$value += count($diff_s)* $this->config['semantic_not_used'];
					$no_semantics++;
				}	
			}

			if(empty($result->headings))
			{
				$value += $this->config['no_headings'];
				$no_headings++;
			}
			else
			{
				//diff_h retourne les balises heading attendues qui ne sont pas présentes dans le document
				$diff_h = array_diff($wanted_headings, $result->headings); 
				if(!empty($diff_h))
				{
					//on retire un demi-point pour chaque balise non utilisée
					$value += count($diff_h)* $this->config['heading_not_used'];
					$no_headings++;
				}
			}

			//test présence balise "style" - on retire 3 points
			if(in_array('style', array_keys($result->stats)))
			{
				$this->decrementScore($resource_name, $this->config['style_tag_used']);
				$with_style++;
			}

			//test si utilisation de l'attribut "class"
			if($result->class_attr === 0)
			{
				$no_class_attr++;
				$this->decrementScore($resource_name, $this->config['class_attr_not_used']);
				$this->addScoringMessage($resource_name, $service_name, '', "L'attribut \"class\" n'est pas utilisé");
			}

			$nb_doc++;
		}


		//on divise par le nombre de document
		$value = round($value / $nb_doc,1);
		$this->decrementScore($resource_name, $value);

		if($no_semantics > 0)
			$this->addScoringMessage($resource_name, $service_name, '', "$no_semantics document(s) avec balises sémantiques manquantes");
		
		if($no_headings > 0)
			$this->addScoringMessage($resource_name, $service_name, '', "$no_headings document(s) avec balises d'en-tête manquantes");
		
		if($with_style > 0)		
			$this->addScoringMessage($resource_name, $service_name, '', "$with_style document(s) avec balise(s) style détectée(s)");				

		if($with_style > 0)		
			$this->addScoringMessage($resource_name, $service_name, '', "$no_class_attr document(s) sans utilser l'attribut 'class'");				


		$this->addScoringMessage($resource_name, $service_name, '', sprintf("<b>scoring : %d </b>", $value));

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
				$value += $this->config['css_has_error'];
				$invalid++;
			}

			$nb_doc++;
		}

		$value = round($value / $nb_doc,1);
		$this->decrementScore($value);
		$this->addScoringMessage($resource_name, $service_name, '', sprintf('%d fichiers css invalides sur %d', $invalid, $nb_doc));
		$this->addScoringMessage($resource_name, $service_name, '', sprintf("<b>scoring : %d </b>", $value));

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
				$value += $this->config['css3_not_used'];
			}
			else{
				$css3++;
				$value -= count($result->css3_rules) * $this->config['css3_rule_used'];

				if($result->css3_no_vendor > 0)
				{
					$no_vendor++;
					$value += count($result->css3_no_vendor) * $this->config['css3_rule_used_no_vendor'];
				}

			}

			if(empty($result->media_queries))
			{
				$value += $this->config['mediaqueries_not_used'];
			}
			else{
				$media_queries++;
				$value -= count($result->media_queries) * $this->config['mediaqueries_rule_used'];
			}

			$nb_doc++;
		}

		$value = round($value / $nb_doc,1);
		$this->decrementScore($value);

		if($css3 === 0)
			$this->addScoringMessage($resource_name, $service_name, '', "pas de règles CSS3 dans le document");
	
		if($media_queries === 0)
			$this->addScoringMessage($resource_name, $service_name, '', "pas de media queries dans le document");
	
		if($no_vendor > 0)
			$this->addScoringMessage($resource_name, $service_name, '', "'Vendor prefix' manquants");
	
		$this->addScoringMessage($resource_name, $service_name, '', sprintf("<b>scoring : %d </b>", $value));

	}


	protected function count_file_scoring($resource){

		$value = 0;

		$html_count = count( $resource->getFiles('*.html') );
		$css_count  = count( $resource->getFiles('*.css') );

		$value -= $html_count * $this->config['html_page'];
		$value -= $css_count * $this->config['css_page'];

		$this->decrementScore($value);
		$this->addScoringMessage($resource->name, "count_file", "", sprintf("%d fichier(s) HTML", $html_count));
		$this->addScoringMessage($resource->name, "count_file", "", sprintf("%d fichier(s) CSS", $css_count));
		
		$this->addScoringMessage($resource->name, 'count file', '', sprintf("<b>scoring : +%d </b>", $value));

	}

	protected function decrementScore($resource_name, $value = 1)
	{	
		$key = $resource_name . "_score";

		\Mudi\Registry::set( $key,   ( \Mudi\Registry::get($key) - $value ) );

	}	

	protected function addScoringMessage($resource_name, $service_name ="", $document_name ="", $message)
	{
		$key = $resource_name . "_scoring_messages";
		if(!\Mudi\Registry::has($key))
		{
			\Mudi\Registry::set($key, array());
		}
		$messages = \Mudi\Registry::get($key);
		$messages[] = implode(' - ', array($service_name , $document_name, $message));
		\Mudi\Registry::set($key, $messages);
	}
}