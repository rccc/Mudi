<?php

if (PHP_SAPI !== 'cli') 
{
    die('Mudi is away');
}

define('DS', DIRECTORY_SEPARATOR);
define('BASE_PATH', __DIR__);
define('MUDI_PATH', BASE_PATH . DS . 'src/Mudi');
define('RESOURCES_PATH', MUDI_PATH . DS . 'Resources');
define('VIEW_PATH', RESOURCES_PATH . DS .'views');
define('TEST_PATH', BASE_PATH . DS . 'tests');

use Cilex\Provider\Console\Adapter\Silex\ConsoleServiceProvider;
use Silex\Application;

require_once __DIR__ . '/vendor/autoload.php';

$app = new Application;

//twig as service
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => array(VIEW_PATH, VIEW_PATH . '/public'),
    'twig.options' => array('autoescape' => false),
    'debug' => false
    ));


//console as service
$app->register(new ConsoleServiceProvider(), array(
    'console.name' => 'Mudi console',
    'console.version' => '0.1.0',
    ));

$app["request"] = array('basepath' => 'http://localhost/mudi/');

$commands = array(
    new \Mudi\Command\ValidateCommand(),
    new \Mudi\Command\TidyCommand(),
    new \Mudi\Command\TagStatsCommand(),
    new \Mudi\Command\CheckLinkCommand(),
    new \Mudi\Command\CasperjsCommand(),
    new \Mudi\Command\CasperjsScreenshotCommand(),
    new \Mudi\Command\RunCommand(),
    new \Mudi\Command\RunAllCommand()
    );

foreach ($commands as $command) {
    $app['console']->add($command);
}

$app['console']->run();
