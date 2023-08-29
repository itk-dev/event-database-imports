<?php

namespace App\MessageHandler;

use App\Message\FeedNormalizationMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class FeedNormalizationHandler
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(FeedNormalizationMessage $message): void
    {
        // Tags normalization.

        // Other normalizations check up. HTML fixer etc.

        throw new UnrecoverableMessageHandlingException('Not implemented yet');
    }
}
