<?php

namespace App\MessageHandler;

use App\Entity\Event;
use App\Message\EventMessage;
use App\Message\FeedItemNormalizationMessage;
use App\Repository\FeedRepository;
use App\Service\ContentNormalizer;
use App\Service\TagsNormalizerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final readonly class FeedItemNormalizationHandler
{
    public function __construct(
        private ContentNormalizer $contentNormalizer,
        private MessageBusInterface $messageBus,
        private TagsNormalizerInterface $tagsNormalizer,
        private FeedRepository $feedRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(FeedItemNormalizationMessage $message): void
    {
        $feedItemData = $message->getFeedItemData();

        // Tags normalization.
        $feedItemData->tags = $this->tagsNormalizer->normalize($feedItemData->tags);

        $feedItemData->url = $feedItemData->url ?? '';

        // Content normalizations check up. HTML fixer etc.
        $feedItemData->description = $this->contentNormalizer->sanitize($feedItemData->description ?? '');

        // Set excerpt from description if empty
        if (empty($feedItemData->excerpt) && !empty($feedItemData->description)) {
            $feedItemData->excerpt = $feedItemData->description;
        }

        if (!empty($feedItemData->excerpt)) {
            try {
                $feedItemData->excerpt = $this->contentNormalizer->sanitize($feedItemData->excerpt);
                $feedItemData->excerpt = $this->contentNormalizer->getTextFromHtml($feedItemData->excerpt);
                $feedItemData->excerpt = $this->contentNormalizer->trimLength($feedItemData->excerpt, Event::EXCERPT_MAX_LENGTH);
            } catch (\Exception $exception) {
                $this->logger->error($exception->getMessage());
            }
        }

        // For feeds delivering plain-text descriptions, convert newlines to <br> so
        // line breaks survive HTML rendering. Done after the excerpt handling above so
        // the excerpt fallback keeps working on the tag-free description.
        $feed = $this->feedRepository->find($feedItemData->feedId);
        if ($feed?->isConvertNewlinesToBr()) {
            $feedItemData->description = $this->contentNormalizer->newlinesToHtml($feedItemData->description ?? '');
        }

        $this->messageBus->dispatch(new EventMessage($feedItemData));
    }
}
