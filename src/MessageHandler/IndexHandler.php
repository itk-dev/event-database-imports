<?php

namespace App\MessageHandler;

use App\Message\IndexMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class IndexHandler
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(IndexMessage $message): void
    {
        // Maybe we need an enum in the message to handle different index types.
        throw new UnrecoverableMessageHandlingException('Not implemented yet');
    }
}
