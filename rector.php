<?php

/** @noinspection DevelopmentDependenciesUsageInspection */

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->withPhpSets()
    ->withPreparedSets(
        deadCode: true,
        privatization: true,
        earlyReturn: true,
        phpunitCodeQuality: true,
        phpunit: true,
    )
;
