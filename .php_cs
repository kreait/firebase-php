<?php

$header = '';

Symfony\CS\Fixer\Contrib\HeaderCommentFixer::setHeader($header);

return Symfony\CS\Config::create()
    ->setUsingCache(true)
    ->level(Symfony\CS\FixerInterface::SYMFONY_LEVEL)
    ->fixers([
        '-psr0',
        //'header_comment',
        '-phpdoc_params',
        '-blankline_after_open_tag',
        'multiline_spaces_before_semicolon',
        'ordered_use',
        'short_array_syntax',
    ])
    ->finder(
        Symfony\CS\Finder::create()
            ->exclude('vendor')
            ->exclude('build')
            ->in(__DIR__)
    );
