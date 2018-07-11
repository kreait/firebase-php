<?php

$finder = PhpCsFixer\Finder::create()
    ->in(['src', 'tests']);

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
