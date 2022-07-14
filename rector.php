<?php

/**
 * @noinspection DevelopmentDependenciesUsageInspection
 * @noinspection TransitiveDependenciesUsageInspection
 */

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $config): void {
    $config->phpVersion(PhpVersion::PHP_74);
    $config->paths([
        __DIR__.'/src',
        __DIR__.'/tests',
        __FILE__,
    ]);

    $config->skip([
        ClosureToArrowFunctionRector::class => [
            __DIR__.'/src/Firebase/Http/Middleware.php'
        ],
    ]);

    // Define what rule sets will be applied
    $config->import(SetList::CODE_QUALITY);
    $config->import(SetList::DEAD_CODE);
    $config->import(SetList::PHP_74);
};
