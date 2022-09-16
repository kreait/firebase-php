<?php

declare(strict_types=1);

use Beste\PhpCsFixer\Config\RuleSet\Php74;
use Ergebnis\PhpCsFixer\Config;

$config = Config\Factory::fromRuleSet(new Php74(), [
    'global_namespace_import' => [
        'import_classes' => true,
        'import_constants' => true,
        'import_functions' => true,
    ],
]);

$config->getFinder()->in(__DIR__);

$config->setCacheFile(__DIR__ . '/tools/.php-cs-fixer.cache');

return $config;
