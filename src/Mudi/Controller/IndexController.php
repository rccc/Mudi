<?php


namespace Mudi\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Mudi\Resource;

class IndexController 
{

	public function index(Request $request, Application $app)
	{
		$form = $app['twig']->render('form.html.twig'); 
		return $app['twig']->render('index.html.twig', array('content' => $form));		
	}

	public function checkResource(Request $request, Application $app)
	{
		/*
		 * Temporaire:
		 * Chaque service est executé l'un à la suite de l'autre
		 * les résultats sot stockés dans des tableaux.
		 *
		 * A terme : 
		 *  - lire un fichier de configs listant les services à utiliser
		 *  - exécuter les services en parallèle
		 *  - stocker les résultats en BDD pour consultation ultérieure des résultats.
		 */
		
		$response = array();
		$file = $request->files->get("resource");
		$tmp  = $file->getPathName();
		$path = $tmp . $file->getClientOriginalName();

		//le path contient désormais le nom original avec l'extension.
		rename($tmp, $path);
		
		$resource = new \Mudi\Resource($path);
		$htmlFiles = $resource->getFiles('html?');

		//on lance la validation
		$validator = new \Mudi\Validator\HtmlServiceValidator();

        foreach($htmlFiles as $file_path => $file) 	    
        {
        	$file_name = pathinfo($file_path)['filename'];
        	$results[$file_name] = $validator->validate($file);
        }		

        $response[] = $app['twig']->render('validation_html.html.twig', array('results'=> $results));
        
        //on vérifie les liens
        $results = array();
        $resource->errors = array();
        $linkChecker = new \Mudi\Link\LinkChecker();
        $res = $resource->getUrls();

        //@todo : revoir la gestion des erreurs @see Mudi\Ressource::getNodeList
        foreach($res as $file_path => $urls)
        {
        	$file_name = pathinfo($file_path)['filename'];

        	if(!empty($urls))
        	{
				$results[$file_name] = $linkChecker->check($urls, $documentPath);
        	}
        	else
        	{
        		$errors[$file_name] = 'Aucun lien trouvé';
        	}
        }        	
       

        $response[] = $app['twig']->render('check_link.html.twig', array('results' => $results, 'errors' => $resource->errors));

        //Usage des balises
        $results = array();
        $tagUsage = new \Mudi\TagUsage();

        $files = $resource->getResourceFilesContent('html?');

        foreach($files as $file_path => $file_content)
        {
        	$file_name = pathinfo($file_path)['filename'];
        	$results[$file_name] = $tagUsage->getUsageStats($file_content);
        }

        $response[] = $app['twig']->render('tag_usage.html.twig', array('results' => $results ));

        $content = implode(PHP_EOL, $response);
       	
       	return $app['twig']->render('index.html.twig' ,array("content" => $content));
	}
}