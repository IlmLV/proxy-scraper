<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in([__DIR__ . '/src', __DIR__ . '/tests'])
    ->append([__FILE__]);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        // Enforce strict (===) comparisons — the codebase already declares strict_types.
        'strict_comparison' => true,
        // Deterministic, alphabetised import ordering and no dead imports.
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        // Drop @param/@return/@var docblocks that only restate a native type hint,
        // while keeping anything that adds information (array shapes, generics, mixed).
        'no_superfluous_phpdoc_tags' => [
            'allow_mixed' => true,
            'remove_inheritdoc' => false,
        ],
        // Clean up after the tag stripping above: drop docblocks left empty and
        // any blank line between a docblock and what it documents.
        'no_empty_phpdoc' => true,
        'phpdoc_trim' => true,
        'no_blank_lines_after_phpdoc' => true,
        'array_syntax' => ['syntax' => 'short'],
        'trailing_comma_in_multiline' => true,
    ])
    ->setFinder($finder);
