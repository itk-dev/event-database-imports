<?php

namespace App\MessageHandler;

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
                $local = $this->imageHandler->fetch($source);
                $image->setLocal($local);
                $this->imageRepository->save($image, true);

                $this->imageHandler->transform($image);
            }
        }

        // @todo: send message to geo-encoder

        // @todo: create next message
        throw new UnrecoverableMessageHandlingException('Not implemented yet');
    }
}
