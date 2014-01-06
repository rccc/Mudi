<?php

namespace Mudi\Service\Validator;

class W3CMarkupValidatorService extends BaseValidatorService
{
	protected $curl;
	protected $results;

	public function __construct()
	{
		$this->name = 'w3c_markup_validator';
	}

	public function validate($file)
	{
		$this->curl = new \RollingCurl\RollingCurl();
		$this->results = new \stdClass();
		//PHP < 5.4
		$self = $this;
		$path = $file;
		$header_size = 0;

		$options = array(
			CURLOPT_HEADER => true,
			CURLOPT_USERAGENT => "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)",
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => array('uploaded_file' => $file, 'output' =>'json', 'debug'=>1, 'verbose'=> 1),
			CURLOPT_RETURNTRANSFER => true,
			)
		;
		$this->curl
			->setOptions($options)
			->setCallback(function(\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl) use($self) {

				$responseErrors = $request->getResponseError();
				//var_dump($request->getResponseText());
				if(empty($responseErrors))
				{

					$infos = $request->getResponseInfo();
					$header_size = $infos['header_size'];
					$header 	 = substr($request->getResponseText(), 0, $header_size);
					$body 		 = json_decode( substr( $request->getResponseText(), $header_size ), true);

					preg_match('/X-W3C-Validator-Status:\s([a-zA-Z]+)/', $header, $result);
					if($result) list(,$status) 	= $result; 
					preg_match('/X-W3C-Validator-Errors:\s(\d+)/', $header, $result);
					if($result) 
						list(,$errors)   = $result;
					else
						$errors = "";
					preg_match('/X-W3C-Validator-Warnings:\s(\d+)/', $header, $result);
					if($result) 
						list(,$warnings)   = $result;
					else
						$warnings = "";
					//@todo "abort status", "recursion"

					$self->results->isValid = ($status === "Valid") ? true : false; 
					$self->results->status 		= $status;
					$self->results->errors 		= $errors;
					$self->results->warnings 	= $warnings;
					$self->results->encoding  	= $body['source']['encoding'];
					$self->results->messages  	= $body['messages'];

				}
				else
				{
					return  false;
				}

			})
			//->post('http://validator.w3.org/check')
			->post('http://localhost/w3c-validator/check')
			->execute();

			return $this->results;
	}

}
