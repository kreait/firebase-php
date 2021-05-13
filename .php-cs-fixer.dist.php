<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(['src', 'tests']);

// https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/master/doc/rules/index.rst
return (new PhpCsFixer\Config())
    ->setUsingCache(true)
    ->setCacheFile(__DIR__.'/tools/php-cs-fixer/.php-cs-fixer.cache')
    ->setRiskyAllowed(true)
    ->setRules([
        '@PhpCsFixer' => true,

        'declare_strict_types' => true, // Force strict types declaration in all files.
        'native_function_invocation' => [ // Add leading \ before function invocation to speed up resolving.
            'include' => [
                '@all',
            ],
            'scope' => 'all',
            'strict' => true,
        ],
        'no_alias_functions' => true, // Master functions shall be used instead of aliases.
        'no_superfluous_phpdoc_tags' => [ // Removes @param, @return and @var tags that don't provide any useful information.
            'allow_mixed' => true,
            'allow_unused_params' => false,
        ],
        'no_useless_sprintf' => true,
        'ordered_class_elements' => [ // Orders the elements of classes/interfaces/traits.
            'order' => ['use_trait'],
        ],
        'phpdoc_align' => [ // All items of the given phpdoc tags must be either left-aligned or (by default) aligned vertically.
            'align' => 'left',
        ],
        'phpdoc_types_order' => [ // Sorts PHPDoc types.
            'sort_algorithm' => 'none',
            'null_adjustment' => 'always_last',
        ],
        'php_unit_internal_class' => true, // All PHPUnit test classes should be marked as internal.
        'php_unit_test_annotation' => true, // Adds or removes @test annotations from tests, following configuration.
        'php_unit_test_class_requires_covers' => false,
        'use_arrow_functions' => true, // Anonymous functions with one-liner return statement must use arrow functions.
        'yoda_style' => [ // Write conditions in Yoda style (true), non-Yoda style (['equal' => false, 'identical' => false, 'less_and_greater' => false]) or ignore those conditions (null) based on configuration.
            'equal' => null,
            'identical' => null,
            'less_and_greater' => null,
            'always_move_variable' => false,
        ],
    ])
    ->setFinder($finder);
