<?php

namespace App\MessageHandler;

use App\Message\GeocoderMessage;
use App\Message\ImageMessage;
use App\Repository\ImageRepository;
use App\Service\ImageHandlerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class ImageHandler
{
    public function __construct(
        private readonly ImageHandlerInterface $imageHandler,
        private readonly ImageRepository $imageRepository,
        private readonly MessageBusInterface $messageBus,
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
                    $this->imageRepository->save($image, true);

                    $this->imageHandler->transform($image);
                } catch (\Exception $e) {
                    throw new UnrecoverableMessageHandlingException(sprintf('Unable to fetch image: %s', $source), (int) $e->getCode(), $e);
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
