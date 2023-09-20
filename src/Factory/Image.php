<?php

namespace App\Factory;

use App\Repository\ImageRepository;
use \App\Entity\Image as ImageEntity;

class Image
{
    public function __construct(
        private readonly ImageRepository $imageRepository,
        private readonly \App\Services\Image $image
    )
    {}

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
