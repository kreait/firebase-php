<?php

/**
 * @noinspection DevelopmentDependenciesUsageInspection
 * @noinspection TransitiveDependenciesUsageInspection
 */

declare(strict_types=1);

use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\Operator\ConcatSpaceFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([
        __DIR__.'/src',
        __DIR__.'/tests',
        __FILE__,
    ]);

    $ecsConfig->parallel();
    $ecsConfig->lineEnding(PHP_EOL);
    $ecsConfig->indentation(Option::INDENTATION_SPACES);

    $ecsConfig->sets([
        SetList::CLEAN_CODE,
        SetList::PSR_12,
        SetList::STRICT,
    ]);

    $ecsConfig->ruleWithConfiguration(OrderedImportsFixer::class, [
        'sort_algorithm' => 'alpha',
    ]);

    $ecsConfig->skip([
        ConcatSpaceFixer::class,
    ]);
};
