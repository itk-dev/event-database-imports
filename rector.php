<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector;
use Rector\Symfony\CodeQuality\Rector\Class_\ControllerMethodInjectionToConstructorRector;
use Rector\Symfony\Symfony73\Rector\Class_\ConstraintOptionsToNamedArgumentsRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->withCache(__DIR__.'/var/cache/rector')
    // Coding style and import (`use`) management are owned by php-cs-fixer
    // (PostToolUse hook / coding standards), so Rector does not touch them.
    //
    // Modernize to the language level declared in composer.json (PHP >= 8.4).
    ->withPhpSets()
    // Version-based upgrade + quality rules resolved from the installed package
    // versions (Doctrine ORM 3 / DBAL 4, Symfony 7.4, PHPUnit 13).
    ->withComposerBased(doctrine: true, symfony: true, phpunit: true)
    // Convert any remaining annotations to native PHP attributes.
    ->withAttributesSets(symfony: true, doctrine: true, phpunit: true)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        privatization: true,
        instanceOf: true,
        earlyReturn: true,
        carbon: true,
        doctrineCodeQuality: true,
        symfonyCodeQuality: true,
        phpunitCodeQuality: true,
    )
    ->withSkip([
        // Rewrites `isset($obj->prop)` to a null comparison that accesses the
        // property directly — unsafe when `$obj` itself is nullable.
        IssetOnPropertyObjectToPropertyExistsRector::class,
        // Flips isset()/type guards in ways that dropped variables and inverted
        // occurrence null-checks (OccurrencesFactory, LocationFactory).
        FlipTypeControlToUseExclusiveTypeRector::class,
        // Turns constraint array options into named args, passing a
        // TranslatableMessage to a `string|null` constructor parameter.
        ConstraintOptionsToNamedArgumentsRector::class,
        // Widened an EasyAdmin filter's `false` label type to `bool`, which the
        // `setLabel(...|false|null)` signature rejects.
        ParamTypeByMethodCallTypeRector::class,
        // The team suppresses `method.unused` (see phpstan.dist.neon) rather than
        // deleting; don't let Rector remove those private methods out from under it.
        RemoveUnusedPrivateMethodRector::class,
        // EasyAdmin CRUD action methods receive framework-provided arguments (e.g.
        // BatchActionDto) and per-action services that are not constructor-autowireable;
        // don't hoist them into the constructor.
        ControllerMethodInjectionToConstructorRector::class,
    ]);
