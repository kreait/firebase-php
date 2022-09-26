<?php

declare(strict_types=1);

use Beste\PhpCsFixer\Config\RuleSet\Php74;
use Ergebnis\PhpCsFixer\Config;

$config = Config\Factory::fromRuleSet(new Php74(), [
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
    'php_unit_test_case_static_method_calls' => [
        'call_type' => 'this',
    ],
]);

$config->getFinder()->in(__DIR__);

$config->setCacheFile(__DIR__ . '/tools/.php-cs-fixer.cache');

return $config;
