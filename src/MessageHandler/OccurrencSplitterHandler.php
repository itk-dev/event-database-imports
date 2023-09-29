<?php

namespace App\MessageHandler;

use App\Message\OccurrenceSplitterMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class OccurrencSplitterHandler
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(OccurrenceSplitterMessage $message): void
    {
        throw new UnrecoverableMessageHandlingException('Not implemented yet');
    }
}
