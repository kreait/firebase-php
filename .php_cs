<?php

return Symfony\CS\Config\Config::create()
    ->finder(
        Symfony\CS\Finder\DefaultFinder::create()
            ->exclude('vendor')
            ->exclude('build')
            ->in(__DIR__)
    )
;