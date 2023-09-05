<?php

namespace App\MessageHandler;

use App\Message\EventMessage;
use App\Message\FeedNormalizationMessage;
use App\Services\ContentNormalizer;
use App\Services\TagsNormalizer;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class FeedNormalizationHandler
{
    public function __construct(
        private readonly ContentNormalizer $contentNormalizer,
        private readonly MessageBusInterface $messageBus,
        private readonly TagsNormalizer $tagsNormalizer,
    ) {
    }

    public function __invoke(FeedNormalizationMessage $message): void
    {
        $item = $message->getItem();

        // Tags normalization.
        $item->tags = $this->tagsNormalizer->normalize($item->tags);

        // Url normalization (relative path to full path)
        // @todo should we detect relative paths?

        // Content normalizations check up. HTML fixer etc.
        // @todo Make field normalization configurable.
        $item->description = $this->contentNormalizer->normalize($item->description);
        $item->excerpt = $this->contentNormalizer->normalize($item->excerpt);
        $item->excerpt = $this->contentNormalizer->trimLength($item->excerpt, 255);

        $this->messageBus->dispatch(new EventMessage($item));
    }
}
