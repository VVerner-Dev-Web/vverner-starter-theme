<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;

return RectorConfig::configure()
  ->withPaths([
    __DIR__ . '/app',
  ])
  ->withPhpSets(true)
  ->withPreparedSets(
    deadCode: true,
    codeQuality: true,
    typeDeclarations: true,
    earlyReturn: true,
    strictBooleans: true
  )
  ->withRules([
    AddVoidReturnTypeWhereNoReturnRector::class,
  ]);
