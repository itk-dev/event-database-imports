<?php

namespace App\Factory;

use App\Entity\Image as ImageEntity;
use App\Repository\ImageRepository;

final readonly class ImageFactory
{
    public function __construct(
        private ImageRepository $imageRepository,
    ) {
    }

    public function createOrUpdate(string $url, ?ImageEntity $image): ImageEntity
    {
        if (is_null($image) || $image->getSource() !== $url) {
            $image = new ImageEntity();
        }
        $image->setSource($url);
        $this->imageRepository->save($image);

        return $image;
    }
}
