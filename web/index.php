<?php

use Coduo\TuTu\Kernel;
use Pimple\Container;
use Symfony\Component\HttpFoundation\Request;

if (is_file($autoload = getcwd() . '/vendor/autoload.php')) {
    require $autoload;
}

if (is_dir($vendor = __DIR__ . '/../vendor')) {
    require($vendor . '/autoload.php');
} elseif (is_dir($vendor = __DIR__ . '/../../..')) {
    require($vendor . '/autoload.php');
} else {
    die(
        'You must set up the project dependencies, run the following commands:' . PHP_EOL .
        'curl -s http://getcomposer.org/installer | php' . PHP_EOL .
        'php composer.phar install' . PHP_EOL
    );
}

$container = new Container();
$container['tutu.root_path'] = realpath(__DIR__ . '/..');
$kernel = new Kernel($container);
$request = Request::createFromGlobals();

$kernel->handle($request)->send();
