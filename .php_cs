<?php

$header = <<<'EOF'
This file is part of the firebase-php package.

(c) Jérôme Gamez <jerome@kreait.com>
(c) kreait GmbH <info@kreait.com>

This source file is subject to the MIT license that is bundled
with this source code in the file LICENSE.
EOF;

Symfony\CS\Fixer\Contrib\HeaderCommentFixer::setHeader($header);

return Symfony\CS\Config\Config::create()
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->fixers([
        'header_comment',
        'multiline_spaces_before_semicolon',
        'newline_after_open_tag',
        'no_blank_lines_before_namespace',
        'ordered_use',
        'phpdoc_order',
        'short_array_syntax',
        'strict',
        'strict_param',
        'unused_use',
    ])
    ->finder(
        Symfony\CS\Finder\DefaultFinder::create()
            ->exclude('vendor')
            ->exclude('build')
            ->in(__DIR__)
    )
;
