<?php

$finder = (new PhpCsFixer\Finder())
  ->in(__DIR__)
  ->exclude([
    'var',
    'migrations',
  ])
;

return (new PhpCsFixer\Config())
  ->setRules([
    '@Symfony' => true,
    '@PhpCsFixer:risky' => true,
    'declare_strict_types' => true,
    'global_namespace_import' => [
      'import_classes' => true,
      'import_constants' => true,
      'import_functions' => true,
    ],
    'phpdoc_to_comment' => [
      'ignored_tags' => ['psalm-suppress'],
    ],
    'nullable_type_declaration_for_default_null_value' => [
      'use_nullable_type_declaration' => true,
    ],
  ])
  ->setIndent('  ')
  ->setFinder($finder)
  ;
  