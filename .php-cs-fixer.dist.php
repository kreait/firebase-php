<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()->in(__DIR__);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER-CS2.0' => true,
        'class_definition' => [
            'single_line' => true,
        ],
        'concat_space' => [
            'spacing' => 'none',
        ],
        'no_unused_imports' => true,
        'ordered_imports' => [
            'imports_order' => [
                'class',
                'function',
                'const',
            ],
            'sort_algorithm' => 'alpha',
        ],
        'php_unit_method_casing' => [
            'case' => 'camel_case',
        ],
        'php_unit_test_case_static_method_calls' => [
            'call_type' => 'this',
            'methods' => [],
        ],
        'single_line_empty_body' => false,
        'yoda_style' => false,
    ])
    ->setFinder($finder);
