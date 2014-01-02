<?php

error_reporting(E_ERROR);
ini_set('display_errors', 1);

define('DS', DIRECTORY_SEPARATOR);
define('BASE_PATH', __DIR__);
define('MUDI_PATH', BASE_PATH . DS . 'src/Mudi');
define('RESOURCES_PATH', MUDI_PATH . DS . 'Resources');
define('VIEW_PATH', RESOURCES_PATH . DS .'views');
define('TEST_PATH', BASE_PATH . DS . 'tests');

use Silex\Application;

require_once __DIR__ . '/vendor/autoload.php';

$app = new Application;
$app['debug'] = true;

//twig as service
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => array(VIEW_PATH , VIEW_PATH . '/public'),
    'twig.options' => array('autoescape' => false)
    ));

//global layout
$app->before(function () use ($app) {
    //$app['twig']->addGlobal('layout', $app['twig']->loadTemplate('index.html.twig'));
    //$app['twig']->addGlobal('content', $app['twig']->loadTemplate('content.html.twig'));
});


//routes
$app->get('/', 'Mudi\\Controller\\IndexController::index');   
$app->post('/check-resource', 'Mudi\\Controller\\IndexController::checkResource');   

$app->run();

