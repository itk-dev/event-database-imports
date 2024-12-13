<?php

namespace App\Factory;

use App\Entity\Image as ImageEntity;
use App\Repository\ImageRepository;
use App\Utils\UriHelper;

final readonly class ImageFactory
{
    public function __construct(
        private ImageRepository $imageRepository,
    ) {
    }

    public function createOrUpdate(string $url, ?ImageEntity $image, ?string $base = null): ImageEntity
    {
        if (is_null($image) || $image->getSource() !== $url) {
            $image = new ImageEntity();
        }
        $image->setSource(UriHelper::getAbsoluteUrl($url, $base));
        $this->imageRepository->save($image);

        return $image;
    }
}
