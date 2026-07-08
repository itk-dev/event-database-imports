<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/src',
    ])
    ->withCache(__DIR__.'/var/cache/rector')
    // Import management is left to php-cs-fixer (PostToolUse hook / coding
    // standards), so Rector does not touch `use` statements.
    // Applies Doctrine upgrade rules based on the installed ORM/DBAL versions.
    ->withComposerBased(doctrine: true)
    ->withSets([
        DoctrineSetList::DOCTRINE_CODE_QUALITY,
    ]);
