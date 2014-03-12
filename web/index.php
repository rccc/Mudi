<?php

error_reporting(E_ERROR);
ini_set('display_errors', 1);

define('DS', DIRECTORY_SEPARATOR);
define('BASE_PATH', dirname(__DIR__));
define('MUDI_PATH', BASE_PATH . DS . 'src/Mudi');
define('RESOURCES_PATH', MUDI_PATH . DS . 'Resources');
define('VIEW_PATH', RESOURCES_PATH . DS .'views');
define('TEST_PATH', BASE_PATH . DS . 'tests');

use Silex\Application;

require_once BASE_PATH . '/vendor/autoload.php';

$app = new Application;
$app['debug'] = true;

//twig as service
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => array(VIEW_PATH , VIEW_PATH . '/public'),
    'twig.options' => array('autoescape' => false)
    ));


//routes
$app->get('/', 'Mudi\\Controller\\IndexController::index');   
$app->post('/check-resource', 'Mudi\\Controller\\IndexController::checkResource');   
$app->get('/show-results', 'Mudi\\Controller\\IndexController::showResults');   
//scoring
$app->register(new Igorw\Silex\ConfigServiceProvider(BASE_PATH  . "/config/mudi.json"));
$app['dispatcher']->addSubscriber( new \Mudi\ScoringSubscriber($app['scoring']) );

$app->run();

