<?php

/**
 * @noinspection DevelopmentDependenciesUsageInspection
 * @noinspection TransitiveDependenciesUsageInspection
 */

declare(strict_types=1);

use PhpCsFixer\Fixer\Alias\NoAliasFunctionsFixer;
use PhpCsFixer\Fixer\ClassNotation\FinalInternalClassFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer;
use PhpCsFixer\Fixer\FunctionNotation\FopenFlagsFixer;
use PhpCsFixer\Fixer\FunctionNotation\NativeFunctionInvocationFixer;
use PhpCsFixer\Fixer\FunctionNotation\NoUselessSprintfFixer;
use PhpCsFixer\Fixer\FunctionNotation\UseArrowFunctionsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\ErrorSuppressionFixer;
use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocAlignFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTypesOrderFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitInternalClassFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitStrictFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitTestAnnotationFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitTestCaseStaticMethodCallsFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitTestClassRequiresCoversFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::INDENTATION, 'spaces');
    $parameters->set(Option::LINE_ENDING, "\n");
    $parameters->set(Option::PARALLEL, true);
    $parameters->set(Option::PATHS, [
        __DIR__.'/src',
        __DIR__.'/tests',
        __FILE__,
    ]);

    $containerConfigurator->import(SetList::PHP_CS_FIXER);
    $containerConfigurator->import(SetList::PHP_CS_FIXER_RISKY);

    $parameters->set(Option::SKIP, [
        FinalInternalClassFixer::class => [
            __DIR__.'/src',
        ],
        PhpUnitTestClassRequiresCoversFixer::class,
        PhpUnitStrictFixer::class,
        ErrorSuppressionFixer::class => [
            __DIR__.'/src/Firebase/Util/Deprecation.php',
        ],
    ]);

    $services = $containerConfigurator->services();
    $services->set(DeclareStrictTypesFixer::class);
    $services->set(FopenFlagsFixer::class)->call('configure', [[
        'b_mode' => true,
    ]]);
    $services->set(NativeFunctionInvocationFixer::class)->call('configure', [[
        'include' => [
            '@all',
        ],
        'scope' => 'all',
        'strict' => true,
    ]]);
    $services->set(NoAliasFunctionsFixer::class);
    $services->set(NoSuperfluousPhpdocTagsFixer::class)->call('configure', [[
        'allow_mixed' => true,
        'allow_unused_params' => false,
    ]]);
    $services->set(NoUselessSprintfFixer::class);
    $services->set(OrderedClassElementsFixer::class)->call('configure', [[
        'order' => ['use_trait'],
    ]]);
    $services->set(PhpdocAlignFixer::class)->call('configure', [[
        'align' => 'left',
    ]]);
    $services->set(PhpdocTypesOrderFixer::class)->call('configure', [[
        'sort_algorithm' => 'none',
        'null_adjustment' => 'always_last',
    ]]);
    $services->set(PhpUnitInternalClassFixer::class);
    $services->set(PhpUnitTestAnnotationFixer::class);
    $services->set(UseArrowFunctionsFixer::class);
    $services->set(YodaStyleFixer::class)->call('configure', [[
        'equal' => null,
        'identical' => null,
        'less_and_greater' => null,
        'always_move_variable' => false,
    ]]);
    $services->set(PhpUnitTestCaseStaticMethodCallsFixer::class)->call('configure', [[
        'call_type' => 'this',
    ]]);
};
