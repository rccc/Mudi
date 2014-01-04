<?php

namespace Mudi\Service\Validator;

class TidyMarkupValidatorService extends BaseValidatorService
{

	public function validateFile($path)
	{
		$result = new \stdClass();

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