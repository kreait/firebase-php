<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return (new Configuration())
    ->addPathToScan(__FILE__, true)
    ->addPathToScan(__DIR__.'/src', false)
    ->addPathToScan(__DIR__.'/tests', true)
    // Functions are currently not detected: https://github.com/shipmonk-rnd/composer-dependency-analyser/issues/67
    ->ignoreErrorsOnPackage('mtdowling/jmespath.php', [ErrorType::UNUSED_DEPENDENCY])
    ->ignoreErrorsOnPackage('google/cloud-firestore', [ErrorType::DEV_DEPENDENCY_IN_PROD])
;
