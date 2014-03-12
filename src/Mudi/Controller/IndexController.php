<?php


namespace Mudi\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
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

        $services = array(

            'Validation_Tidy'            => array(
                'ProxyService' => '\Mudi\ProxyService\TidyProxyService',
                'template' => 'tidy.html.twig'),/*
            'Validation_HTML'           => array(
                'ProxyService' => '\Mudi\ProxyService\W3CMarkupValidatorProxyService',
                'template' => 'validation-w3c.html.twig'),
            */
            
            
            'Vérification_liens'    => array(
                'ProxyService' => '\Mudi\ProxyService\LinkCheckerProxyService',
                'template' => 'check_link.html.twig'),  
            
        
            /*
            'Stats_balises'     => array(
                'ProxyService' => '\Mudi\ProxyService\TagUsageProxyService',
                'template' => 'tag_usage.html.twig'),
            */

            /*
            'Validation_CSS'            => array(
                'ProxyService' => '\Mudi\ProxyService\W3CCssValidatorProxyService',
                'template' => 'validation-w3c-css.html.twig'),
            'CSS_Usage' => array(
                'ProxyService' => '\Mudi\ProxyService\CssUsageProxyService',
                'template'     => 'css_usage.html.twig'
                ),
            'Screenshot'                => array(
                'ProxyService' => '\Mudi\ProxyService\ScreenshotProxyService',
                'template' => 'screenshot.html.twig',
                )
            */ 
            )
        ;

		//le path contient désormais le nom original avec l'extension.
		rename($tmp, $path);
		
		$resource = new \Mudi\Resource($path);

        foreach($services as $service_name => $data)
        {

            $options['resource'] = $resource;

            if($service_name === 'Validation_HTML')
            {
                $options['service_url'] = $container['html_validation_url'];
            }
            elseif($service_name === 'Validation_CSS')
            {
                $options['service_url'] = $container['css_validation_url'];
            }
            elseif($service_name === "Screenshot")
            {
                $options['output_dir'] = $resource_output;
            }

            $proxy = new $data['ProxyService']($options);
            
            $results  = $proxy->execute();

            $array[] = $app['twig']->render($data['template'], array('results' => $results->all() )); 
            $app['dispatcher']->dispatch('service.done', new \Mudi\Event($proxy));

        }
		

        //score
        $score = \Mudi\Registry::get($resource->name . '_score');
        $scoring_messages = \Mudi\Registry::get($resource->name . '_scoring_messages');

        \Mudi\Registry::del($resource->name . '_score');
        \Mudi\Registry::del($resource->name . '_scoring_message');

        $score_tpl = $app['twig']->render('score.html.twig', array(
            'score' => $score, 
            'messages' => $scoring_messages,
            'resource_name' => $resource->name
            )
        );
        array_unshift($array, $score_tpl);

        if($resource->isArchive)
        {
            $resource->delete_archive();
        }

        $app['results_content'] = implode(PHP_EOL, $array);

        $subRequest = Request::create('/show-results', 'GET');
        return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

	}

    public function showResults(Request $request, Application $app)
    {
        return $app['twig']->render('index.html.twig', array('content' => $app['results_content']));        
    }
}