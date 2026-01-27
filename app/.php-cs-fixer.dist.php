<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
    ->exclude('vendor')
    ->exclude('config/jwt')
    ->notPath('config/bundles.php')
    ->notPath('config/preload.php')
    ->notPath('public/index.php')
;

return (new PhpCsFixer\Config())
    ->setRules([
        // Standards (layered: PER-CS 2.0 + Symfony + PHP 8.4)
        '@PER-CS2x0' => true,
        '@PER-CS2x0:risky' => true,
        '@PHP8x4Migration' => true,
        '@Symfony:risky' => true,
        '@PHP84Migration' => true,
        '@DoctrineAnnotation' => true,

        // Array notation
        'array_syntax' => ['syntax' => 'short'],
        'list_syntax' => ['syntax' => 'short'],
        'normalize_index_brace' => true,

        // Imports
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => false,
            'import_functions' => false,
        ],
        'no_unused_imports' => true,

        // PHPDoc
        'phpdoc_order' => true,
        'phpdoc_separation' => true,
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_summary' => false,
        'phpdoc_to_comment' => false,
        'no_superfluous_phpdoc_tags' => ['allow_mixed' => true],

        // Spacing and formatting
        'concat_space' => ['spacing' => 'one'],
        'binary_operator_spaces' => [
            'default' => 'single_space',
        ],
        'single_line_throw' => false,
        'yoda_style' => false,

        // Class notation
        'class_attributes_separation' => [
            'elements' => [
                'const' => 'one',
                'method' => 'one',
                'property' => 'one',
            ],
        ],
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'case',
                'constant_public',
                'constant_protected',
                'constant_private',
                'property_public',
                'property_protected',
                'property_private',
                'construct',
                'destruct',
                'magic',
                'phpunit',
                'method_public',
                'method_protected',
                'method_private',
            ],
        ],

        // Strict types
        'declare_strict_types' => true,
        'strict_param' => true,

        // Modernization
        'modernize_types_casting' => true,
        'modernize_strpos' => true,

        // Nullable type declaration
        'nullable_type_declaration_for_default_null_value' => true,

        // Trailing comma in multiline
        'trailing_comma_in_multiline' => [
            'elements' => ['arrays', 'arguments', 'parameters'],
        ],
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
    ->setCacheFile(__DIR__ . '/var/.php-cs-fixer.cache')
;
