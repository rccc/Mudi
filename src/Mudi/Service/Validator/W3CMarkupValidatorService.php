<?php

namespace Mudi\Service\Validator;

class W3CMarkupValidatorService extends BaseValidatorService
{
	const DEFAULT_URL = 'http://validator.w3.org/check';
	
	protected $curl;
	protected $options;
	protected $results;
	protected $service_url;


	public function __construct($options = array())
	{
		$this->name = 'w3c_markup_validator';
		$this->options = $options;
		$this->service_url =  !empty($this->options['service_url'])? $this->options['service_url'] : self::DEFAULT_URL;
	}

	public function validate($file)
	{

		$this->curl = new \RollingCurl\RollingCurl();
		$this->result = new \Mudi\Result\ValidatorResult();
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
				if(empty($responseErrors))
				{

					$infos = $request->getResponseInfo();
					$header_size = $infos['header_size'];
					$header 	 = substr($request->getResponseText(), 0, $header_size);
					$body 		 = json_decode( substr( $request->getResponseText(), $header_size ), true);

					$status = '';
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

					$self->result->isValid 	= ($status === "Valid") ? true : false; 
					$self->result->status 	= $status;
					$self->result->error_count = $errors;
					$self->result->warning_count 	= $warnings;
					$self->result->encoding  	= $body['source']['encoding'];

					if(!empty($body['messages']))
					{
						foreach($body['messages'] as $message)
						{
							$self->result->messages[]  	= $message;
						}
					}

				}
				else
				{
					return  false;
				}

			})
			->post($this->service_url)
			->execute();

			return $this->result;
	}

}
