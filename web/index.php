<?php

use Coduo\TuTu\Kernel;
use Coduo\TuTu\ServiceContainer;
use Symfony\Component\HttpFoundation\Request;

if (is_file($autoload = getcwd() . '/vendor/autoload.php')) {
    require $autoload;
}

if (is_file($autoload = __DIR__ . '/../vendor/autoload.php')) {
    require($autoload);
} elseif (is_file($autoload = __DIR__ . '/../../../autoload.php')) {
    require($autoload);
} else {
    header("Content-Type:text/plain");
    die (
        'You must set up the project dependencies, run the following commands:' . PHP_EOL .
        'curl -s http://getcomposer.org/installer | php' . PHP_EOL .
        'php composer.phar install' . PHP_EOL
    );
}

$container = new ServiceContainer();
$container->setParameter('tutu.root_path', realpath(__DIR__ . '/..'));
$kernel = new Kernel($container);
$request = Request::createFromGlobals();

$kernel->handle($request)->send();
