<?php

namespace App\MessageHandler;

use App\Entity\Event;
use App\Message\EventMessage;
use App\Message\FeedNormalizationMessage;
use App\Service\ContentNormalizer;
use App\Service\TagsNormalizerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final readonly class FeedNormalizationHandler
{
    public function __construct(
        private ContentNormalizer $contentNormalizer,
        private MessageBusInterface $messageBus,
        private TagsNormalizerInterface $tagsNormalizer,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(FeedNormalizationMessage $message): void
    {
        $item = $message->getItem();

        // Tags normalization.
        $item->tags = $this->tagsNormalizer->normalize($item->tags);

        $item->url = $item->url ?? '';

        // Content normalizations check up. HTML fixer etc.
        $item->description = $this->contentNormalizer->sanitize($item->description ?? '');

        // Set excerpt from description if empty
        if (empty($item->excerpt) && !empty($item->description)) {
            $item->excerpt = $item->description;
        }

        if (!empty($item->excerpt)) {
            try {
                $item->excerpt = $this->contentNormalizer->sanitize($item->excerpt);
                $item->excerpt = $this->contentNormalizer->getTextFromHtml($item->excerpt);
                $item->excerpt = $this->contentNormalizer->trimLength($item->excerpt, Event::EXCERPT_MAX_LENGTH);
            } catch (\Exception $exception) {
                $this->logger->error($exception->getMessage());
            }
        }

        $this->messageBus->dispatch(new EventMessage($item));
    }
}
