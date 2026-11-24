<?php

use Rector\Config\RectorConfig;
use Rector\PHPUnit\CodeQuality\Rector\StmtsAwareInterface\DeclareStrictTypesTestsRector;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\SafeDeclareStrictTypesRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/config',
        __DIR__.'/public',
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->withRootFiles()
    ->withSkip([
        __DIR__.'/config/bundles.php',
        __DIR__.'/config/reference.php',
        // Symfony code and recipes don't declare strict types.
        SafeDeclareStrictTypesRector::class,
        DeclareStrictTypesTestsRector::class,
    ])
    ->withCache(__DIR__.'/var/cache/rector')
    ->withSymfonyContainerXml(__DIR__.'/var/cache/dev/App_KernelDevDebugContainer.xml')
    ->withPhpSets()
    ->withComposerBased(twig: true, doctrine: true, phpunit: true, symfony: true)
    ->withAttributesSets()
    // No codingStyle set: formatting is php-cs-fixer's job.
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        typeDeclarationDocblocks: true,
        privatization: true,
        instanceOf: true,
        earlyReturn: true,
        phpunitCodeQuality: true,
        doctrineCodeQuality: true,
        symfonyCodeQuality: true,
        symfonyConfigs: true,
    )
    ->withImportNames(importShortClasses: false)
;
