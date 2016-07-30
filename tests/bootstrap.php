<?php

error_reporting(E_ALL);

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../vendor/autoload.php';

$loader->addPsr4('Tests\\Firebase\\', __DIR__.'/Firebase');
$loader->addClassMap([
    'Tests\\FirebaseTestCase' => __DIR__.'/FirebaseTestCase.php',
    'Tests\\FirebaseTest' => __DIR__.'/FirebaseTest.php',
]);
