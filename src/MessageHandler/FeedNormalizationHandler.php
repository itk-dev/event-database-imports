<?php

namespace App\MessageHandler;

use App\Message\EventMessage;
use App\Message\FeedNormalizationMessage;
use App\Service\ContentNormalizer;
use App\Service\TagsNormalizerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class FeedNormalizationHandler
{
    public function __construct(
        private readonly ContentNormalizer $contentNormalizer,
        private readonly MessageBusInterface $messageBus,
        private readonly TagsNormalizerInterface $tagsNormalizer,
        private readonly int $excerptMaxLength,
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
        $item->description = $this->contentNormalizer->sanitize($item->description ?? '');
        if (!empty($item->excerpt)) {
            $item->excerpt = $this->contentNormalizer->sanitize($item->excerpt);
            $item->excerpt = $this->contentNormalizer->trimLength($item->excerpt, $this->excerptMaxLength);
        }

        $this->messageBus->dispatch(new EventMessage($item));
    }
}
