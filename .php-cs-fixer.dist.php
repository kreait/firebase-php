<?php

declare(strict_types=1);

use Beste\PhpCsFixer\Config\Factory;
use Beste\PhpCsFixer\Config\RuleSet\Php81;

$config = Factory::fromRuleSet(new Php81(), [
    'final_class' => false,
    'final_internal_class' => false,
    'global_namespace_import' => [
        'import_classes' => true,
        'import_constants' => true,
        'import_functions' => true,
    ],
    'php_unit_method_casing' => [
        'case' => 'camel_case',
    ],
    'php_unit_test_case_static_method_calls' => [
        'call_type' => 'this',
        'methods' => [],
    ],
    'php_unit_test_class_requires_covers' => false,
    'yoda_style' => false,
]);

$config
    ->getFinder()
    ->in([
        'src',
        'tests',
    ])
    ->ignoreDotFiles(false)
;

return $config;
