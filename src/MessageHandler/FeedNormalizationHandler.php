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
    /**
     * Max length here is taken from the max database varchar length.
     */
    private const EXCERPT_MAX_LENGTH = 255;

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
        $item->description = $this->contentNormalizer->normalize($item->description ?? '');
        if (!is_null($item->excerpt)) {
            $item->excerpt = $this->contentNormalizer->normalize($item->excerpt);
            $item->excerpt = $this->contentNormalizer->trimLength($item->excerpt, self::MAX_LENGTH);
        }

        $this->messageBus->dispatch(new EventMessage($item));
    }
}
