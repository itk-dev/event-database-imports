<?php

namespace App\Factory;

use App\Entity\Image as ImageEntity;
use App\Repository\ImageRepository;

final class ImageFactory
{
    public function __construct(
        private readonly ImageRepository $imageRepository,
    ) {
    }

    public function createOrUpdate(string $url, ?ImageEntity $image): ImageEntity
    {
        if (is_null($image) || $image->getSource() !== $url) {
            $image = new ImageEntity();
            $image->setSource($url);
        } else {
            // @todo: should we soft delete and create new image.
            $image->setSource($url);
            $image->setUpdated(true);
        }
        $this->imageRepository->save($image);

        return $image;
    }
}
