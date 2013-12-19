<?php

namespace Mudi\Validator;

class HtmlServiceValidator extends BaseValidator
{
	protected $curl;
	protected $results;

	public function __construct()
	{
		$this->results = array();
		$this->curl = new \RollingCurl\RollingCurl();
		$this->curl
			->setSimultaneousLimit(10)
		;

	}

	public function validate($file)
	{
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
					$body 		 = substr($request->getResponseText(), $header_size);

					preg_match('/X-W3C-Validator-Status:\s([a-zA-Z]+)/', $header, $result);
					list(,$status) 	= $result; 
					preg_match('/X-W3C-Validator-Errors:\s(\d+)/', $header, $result);
					list(,$errors)   = $result;
					preg_match('/X-W3C-Validator-Warnings:\s(\d+)/', $header, $result);
					list(,$warnings) = $result;
						//@todo "abort status", "recursion"

					$self->results =  array(
						'response_body' => json_decode($body, true),
						'status' 		=> $status,
						'errors' 		=> $errors,
						'warnings' 		=> $warnings
						);

				}
				else
				{
					return  false;
				}

			})
			->post('http://validator.w3.org/check')
			->execute();

			return $this->results;
	}

}
