<?php

namespace App\MessageHandler;

use App\Message\EventMessage;
use App\Message\FeedNormalizationMessage;
use App\Services\Feeds\TagsNormalizerService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class FeedNormalizationHandler
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly TagsNormalizerService $tagsNormalizerService,
    ) {
    }

    public function __invoke(FeedNormalizationMessage $message): void
    {
        $item = $message->getItem();

        // Tags normalization.
        $item->tags = $this->tagsNormalizerService->normalize($item->tags);

        // Url normalization (relative path to full path)

        // Content normalizations check up. HTML fixer etc.
        // Strip tags config

        $this->messageBus->dispatch(new EventMessage($item));
    }
}
