<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude(['build', 'vendor'])
    ->in(__DIR__);

return PhpCsFixer\Config::create()
    ->setUsingCache(true)
    ->setRules([
        '@Symfony' => true,
        'header_comment' => ['header' => ''],
        'phpdoc_align' => false,
        'phpdoc_order' => true,
        'ordered_imports' => true,
        'array_syntax' => ['syntax' => 'short'],
    ])
    ->setFinder($finder);
