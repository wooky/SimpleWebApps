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
  ])
  ->setIndent('  ')
  ->setFinder($finder)
  ;
  