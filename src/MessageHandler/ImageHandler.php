<?php

namespace App\MessageHandler;

use App\Exception\FilesystemException;
use App\Exception\ImageFetchException;
use App\Message\GeocoderMessage;
use App\Message\ImageMessage;
use App\Repository\ImageRepository;
use App\Service\Image;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AsMessageHandler]
final class ImageHandler
{
    public function __construct(
        private readonly Image $imageService,
        private readonly ImageRepository $imageRepository,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    /**
     * @throws ImageFetchException
     * @throws FilesystemException
     * @throws TransportExceptionInterface
     */
    public function __invoke(ImageMessage $message): void
    {
        $imageId = $message->getImageId();

        if (!is_null($imageId)) {
            $image = $this->imageRepository->findOneBy(['id' => $imageId]);
            if (!is_null($image)) {
                $local = $this->imageService->fetch($image->getSource());
                $image->setLocal($local);
                $this->imageRepository->save($image, true);

                $this->imageService->transform($image);
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
