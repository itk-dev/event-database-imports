<?php

namespace App\Factory;

use App\Entity\Image as ImageEntity;
use App\Repository\ImageRepository;

class Image
{
    public function __construct(
        private readonly ImageRepository $imageRepository,
        private readonly \App\Services\Image $image
    ) {
    }

    public function createOrUpdate(string $url, ?ImageEntity $image): ImageEntity
    {
        if (is_null($image)) {
            $image = new ImageEntity();
            $image->setSource($url);
        } elseif ($image->getSource() !== $url) {
            $image = new ImageEntity();
            $image->setSource($url);
        } else {
            // Check if image have been updated.
        }

        return $image;
    }
}
