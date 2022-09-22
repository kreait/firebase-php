<?php

/** @noinspection DevelopmentDependenciesUsageInspection */

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    $rectorConfig->cacheDirectory(__DIR__ . '/tools/.rector-cache');

    $rectorConfig->importNames();

    // register a single rule
    // $rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);

    // define sets of rules
    $rectorConfig->sets([
        SetList::EARLY_RETURN,
        LevelSetList::UP_TO_PHP_74,
    ]);

    $rectorConfig->phpstanConfig(__DIR__ . '/phpstan-for-rector.neon.dist');
};
