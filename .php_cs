<?php

return Symfony\CS\Config\Config::create()
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->finder(
        Symfony\CS\Finder\DefaultFinder::create()
            ->exclude('vendor')
            ->exclude('build')
            ->in(__DIR__)
    )
;