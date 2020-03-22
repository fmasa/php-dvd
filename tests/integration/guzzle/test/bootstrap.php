<?php

use DVD\Example\ExampleHttpClient;

require_once __DIR__ . '/../../../../vendor/autoload.php';
$loader = require_once 'vendor/autoload.php';

/**
 * @var \Composer\Autoload\ClassLoader
 */
$loader->addClassMap(array(
    ExampleHttpClient::class => 'ExampleHttpClient.php'
));

\DVD\DVD::turnOn();
\DVD\DVD::turnOff();
