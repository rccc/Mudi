<?php

namespace Mudi\Service\Validator;

class BaseValidatorService 
{

	public function getResponseHeaders($content, $header_size)
	{
		return substr($content, 0, $header_size);
	}

	public function getStatusFromHeaders($header)
	{
		preg_match('/X-W3C-Validator-Status:\s([a-zA-Z]+)/', $header, $result);

		if(!empty($result)) 
		{
			list(,$status) 	= $result;
			return $status;
		} 

		return false;
	}

	public function getWarningsFromHeaders($header)
	{
		preg_match('/X-W3C-Validator-Warnings:\s(\d+)/', $header, $result);
		if($result) 
		{
			list(,$warnings)   = $result;
			return $warnings;
		}

		return false;
	}

	public function getErrorsFromHeaders($header)
	{
		preg_match('/X-W3C-Validator-Errors:\s(\d+)/', $header, $result);
		if($result) 
		{
			list(,$errors)   = $result;
			return $errors;
		}

		return false;

	}

	public function getResponseBody($content, $header_size)
	{
		return substr($content, $header_size );
	}

	public function getErrors($body){

		$array = array();

		$xml = simplexml_load_string($body);
		
		$xml->registerXPathNamespace("m", "http://www.w3.org/2005/07/css-validator");

		$errors = $xml->xpath('//m:error/m:message');

		if(!empty($errors))
		{
			foreach($errors as $error)
			{	
				$array[] = trim($error->__toString());
			}			
		}
		
		return $array;

	}

	public function getWarnings($body)
	{

	}

}