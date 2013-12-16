<?php

use Cilex\Provider\Console\Adapter\Silex\ConsoleServiceProvider;
use Silex\Application;

require_once __DIR__ . '/vendor/autoload.php';

$app = new Application;

// Omitted Silex route definitions

//$app->register(new Silex\Provider\ServiceControllerServiceProvider());


// Console Service Provider and command-line commands
$app->register(new ConsoleServiceProvider(), array(
    'console.name' => 'Mudi console',
    'console.version' => '0.1.0',
));


$commands = array(
    new \Mudi\Command\ValidateCommand(),
    new \Mudi\Command\TagStatsCommand(),
    new \Mudi\Command\CheckLinkCommand()
);

foreach ($commands as $command) {
    $app['console']->add($command);
}

if (PHP_SAPI !== 'cli') {
  $app->run();
} else {
  $app['console']->run();
}
