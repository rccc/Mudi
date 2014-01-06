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

		foreach($results as $document_name => $result)
		{
			$i = 0;

			if(!empty($result->urls))
			{
				foreach($result->urls as $url => $link)
				{	
					if(false === $link->exists)
					{	
						$i++;
					}
				}
				if($i > 0)
				{
					$this->decrementScore($resource_name, 1);
					$this->addScoringMessage($resource_name, $service_name, $document_name, sprintf("%d lien(s) cassé(s) (%d)", $i, 1));
				}
			}
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
				if($errors > 10)
				{
					$value = 5;
				}
				else
				{
					$value = $errors * 0.5;
				}
				$this->decrementScore($resource_name, $value);
				$this->addScoringMessage($resource_name, $service_name, $document_name, "$errors erreur(s)");
			}
		}
	}

	protected function tidy_validator_scoring($service_name, $resource_name, $results)
	{
		//var_dump("tidy_validator_scoring");

		$n = 0;
		foreach($results as $document_name => $result)
		{	
			if($result->count_errors > 0) $n++;

			if($result->count_errors > 3)
			{
				$value = 3;
			}
			elseif($result->count_errors > 0 && $result->count_errors <= 3)
			{
				static $i = 0;
				$this->decrementScore($resource_name, $result->count_errors);
				$i++;
			}
		}
		$this->addScoringMessage($resource_name, $service_name, $document_name, "$n document(s) non valide(s)");
	}

	protected function tag_usage_scoring($service_name, $resource_name, $results)
	{
		//var_dump('tag_usage_scoring');

		$wanted_tags = array('header', 'footer', 'article', 'section','aside', 'nav', 'video', 'audio', 'canevas');
		
		foreach($results as $document_name => $result)
		{
			$tags = array_keys($result->stats);
			$test = array_intersect($wanted_tags, $tags); 
			if(empty($test))
			{
				$this->decrementScore($resource_name, 1);
				$this->addScoringMessage($resource_name, $service_name, $document_name, 'Aucune balise HTML5 (-1)');
			}
		}
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