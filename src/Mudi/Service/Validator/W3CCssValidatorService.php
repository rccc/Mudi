<?php

namespace Mudi\Service\Validator;

class W3CCssValidatorService extends BaseValidatorService
{
	
	const DEFAULT_URL = 'http://jigsaw.w3.org/css-validator/validator';
	protected $options;
	protected $result;
	protected $service_url;

	public function __construct($options = array())
	{
		$this->name = 'w3c_css_validator';
		$this->options = $options;
		$this->result = new \Mudi\Result\ValidatorResult();
		$this->service_url = !empty($this->options['service_url'])? $this->options['service_url'] : self::DEFAULT_URL;
	}

	public function validate($file_content)
	{

		$options = array(
			CURLOPT_HEADER => true,
			CURLOPT_USERAGENT => "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)",
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => array('text' => $file_content, 'profile' => 'css3','output' =>'soap12', 'lanf'=>'fr', 'warnings'=> 2),
			CURLOPT_RETURNTRANSFER => true,
			)
		;
	
		//$ch = curl_init('http://aleph0.fr:8007/css-validator/#validate_by_upload');
		$ch = curl_init($this->service_url);
		curl_setopt_array($ch, $options);

		$response = curl_exec($ch);

		if(false === $response)
		{
			$this->result->messages[] = curl_error($ch);
			return $this->result;
		}

		$header_size 					= curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$header 						= $this->getResponseHeaders($response, $header_size);
		$this->result->error_count 		= $this->getErrorsFromHeaders($header);
		$this->result->warning_count 	= $this->getWarningsFromHeaders($header);
		$this->result->status 			= $this->getStatusFromHeaders($header);

		$this->result->isValid = $this->result->status === "Valid" ? true : false;

		if($this->result->error_count > 0)
		{
			$body 	= $this->getResponseBody($response, $header_size);
			$errors = $this->getErrors($body);

			if(!empty($errors))
			{
				foreach($errors as $error)
				{
					$this->result->messages[] = $error;
				}
			}

		}

		curl_close($ch);

		return $this->result;
	}

}
