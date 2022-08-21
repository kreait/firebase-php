<?php

declare(strict_types=1);

use Ergebnis\PhpCsFixer\Config;

$config = Config\Factory::fromRuleSet(new Config\RuleSet\Php74(), [
    'blank_line_between_import_groups' => true,
    'phpdoc_line_span' => false,
    'concat_space' => [
        'spacing' => 'none',
    ],
    'final_class' => false,
    'final_internal_class' => false,
    'final_public_method_for_abstract_class' => false,
    'global_namespace_import' => [
        'import_classes' => true,
        'import_constants' => true,
        'import_functions' => true,
    ],
    'phpdoc_align' => [
        'align' => 'left',
    ],
    'phpdoc_types_order' => [
        'null_adjustment' => 'always_last',
        'sort_algorithm' => 'none',
    ],
    'php_unit_test_case_static_method_calls' => [
        'call_type' => 'this',
    ],
    'php_unit_test_class_requires_covers' => false,
    'yoda_style' => false,
]);

$config->getFinder()->in(__DIR__);

$config->setCacheFile(__DIR__ . '/tools/.php-cs-fixer.cache');

return $config;
