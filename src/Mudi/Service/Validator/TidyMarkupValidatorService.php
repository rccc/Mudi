<?php

namespace Mudi\Service\Validator;

use Symfony\Component\Process\Process;

class TidyMarkupValidatorService extends BaseValidatorService
{

	public function __construct()
	{
		$this->name = "tidy_validator";
	}

	public function validateFile($path)
	{
		$result = new \stdClass();
		$result->status = 0;
		$result->errors = array();
		$result->count_errors = 0;

		//validation html5
		$process = new Process(sprintf('tidy -eq "%s"', $path));
		$process->run();
		$errors = $this->getErrorMessages($process->getErrorOutput());

		//validation xml 
		$process = new Process(sprintf('tidy -xml -q "%s"', $path));
		$process->run();
		$errors = array_merge( $this->getErrorMessages($process->getErrorOutput()), $errors);

		if(!empty($errors))
		{
			$result->status = 2;
			$result->errors = $errors;
			$result->count_errors = count($errors);
		}

		/*
		$options = array(
			'hide-comments'         => true,
			'tidy-mark'             => false,
			'indent'                => true,
			'indent-spaces'         => 4,
			'new-blocklevel-tags'  	=> 'article aside audio details figcaption figure footer header hgroup nav section source summary temp track video',
			'new-empty-tags' 		=> 'command embed keygen source track wbr',
			'new-inline-tags' 		=> 'audio canvas command datalist embed keygen mark meter output progress time video wbr',
			'doctype'               => '<!DOCTYPE HTML>',
			'vertical-space'        => false,
			'output-xhtml'          => false,
			'wrap'                  => 180,
			'wrap-attributes'       => false,
			'break-before-br'       => false,
			'char-encoding'         => 'utf8',
			'input-encoding'        => 'utf8',
			'output-encoding'       => 'utf8',
			'input-xml'				=> 0
			);

		$tidy = new \Tidy;

		$tidy->parseFile($path, $options);

		$tidy->cleanRepair();
		$tidy->diagnose();
		$result->status 		= $tidy->getStatus();
		$result->count_errors 	= tidy_error_count($tidy);
		$result->count_warnings = tidy_warning_count($tidy);
		$result->count_access 	= tidy_access_count($tidy);
		$result->errors   		= $this->getErrorMessages($tidy->errorBuffer);
		$result->infos 			= $this->getInfoMessages($tidy->errorBuffer);
		
		*/

		return $result;
	}

	/**
	 * [getErrorMessage description]
	 * @return Array [description]
	 */
	public function getErrorMessages($messages)
	{
		preg_match_all('/^line.+/im', $messages, $errors);
		return $errors[0];
	}

	/**
	 * [getInfoMessages description]
	 * @param  String $messages [description]
	 * @return Array  [description]
	 */
	public function getInfoMessages($messages)
	{
		preg_match_all('/^info.+/im', $messages, $infos);

		return $infos[0];
	}

}