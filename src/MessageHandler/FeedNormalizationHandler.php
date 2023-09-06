<?php

namespace App\MessageHandler;

use App\Message\EventMessage;
use App\Message\FeedNormalizationMessage;
use App\Services\ContentNormalizer;
use App\Services\TagsNormalizerService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class FeedNormalizationHandler
{
    public function __construct(
        private readonly ContentNormalizer $contentNormalizer,
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
        // @todo should we detect relative paths?

        // Content normalizations check up. HTML fixer etc.
        // @todo Make field normalization configurable.
        $item->description = $this->contentNormalizer->normalize($item->description);
        $item->excerpt = $this->contentNormalizer->normalize($item->excerpt);

        $this->messageBus->dispatch(new EventMessage($item));
    }
}
