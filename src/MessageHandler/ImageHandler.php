<?php

namespace App\MessageHandler;

use App\Message\GeocoderMessage;
use App\Message\ImageMessage;
use App\Repository\ImageRepository;
use App\Service\ImageHandlerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final readonly class ImageHandler
{
    public function __construct(
        private ImageHandlerInterface $imageHandler,
        private ImageRepository $imageRepository,
        private MessageBusInterface $messageBus,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(ImageMessage $message): void
    {
        $imageId = $message->getImageId();

        if (!is_null($imageId)) {
            $image = $this->imageRepository->findOneBy(['id' => $imageId]);
            $source = $image?->getSource();
            if (isset($image, $source)) {
                try {
                    $local = $this->imageHandler->fetch($source);
                    $image->setLocal($local);

                    $image->setTitle(basename($source));

                    $this->imageRepository->save($image, true);

                    $this->imageHandler->transform($image);
                } catch (\Exception $e) {
                    // Indexing should continue even if we cannot fetch the image
                    $this->logger->info(sprintf('Unable to fetch remote image: %d', $source));
                }
            }
        }

        if (!is_null($message->getEventId())) {
            // Send message to the next step in the message import chain.
            $this->messageBus->dispatch(new GeocoderMessage($message->getEventId()));
        } else {
            throw new UnrecoverableMessageHandlingException('Missing event id in image handler');
        }
    }
}
