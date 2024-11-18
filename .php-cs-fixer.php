<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__);

return (new PhpCsFixer\Config())
    ->setRules([
        'braces' => [
            'allow_single_line_closure' => true, // Allow single-line if, for, etc.
            'position_after_functions_and_oop_constructs' => 'same',
        ],
        'indentation_type' => true,
        'no_trailing_whitespace' => true,
        'single_quote' => true,
        'array_syntax' => ['syntax' => 'short'],
    ])
    ->setFinder($finder);
