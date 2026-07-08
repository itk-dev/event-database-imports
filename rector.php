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
    // Safe on the current stack. The Doctrine ORM 3 / DBAL 4 upgrade sets are
    // intentionally NOT enabled here — enable them in the upgrade PR, once the
    // dependencies are bumped, so Rector rewrites for the versions actually
    // installed:
    //
    //     DoctrineSetList::DOCTRINE_DBAL_40
    //     DoctrineSetList::DOCTRINE_ORM_300
    //     DoctrineSetList::DOCTRINE_COLLECTION_22
    ->withSets([
        DoctrineSetList::DOCTRINE_CODE_QUALITY,
    ]);
