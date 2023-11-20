<?php

namespace App\Service;

use App\Entity\Image;

interface ImageHandlerInterface
{
    /**
     * Fetch image from remote server.
     *
     * @param string $url
     *  Where to download the image from
     *
     * @return string
     *   Relative path to the downloaded file
     */
    public function fetch(string $url): string;

    /**
     * Remove image from the filesystem.
     *
     * @param Image $image
     *   The image to remove
     *
     * @return bool
     *   True if image was removed else false
     */
    public function remove(Image $image): bool;

    /**
     * Make image transformations.
     *
     * @param Image $image
     *   The image to transform
     */
    public function transform(Image $image): void;

    /**
     * Get urls for derived images from image url.
     *
     * @param string $imageUrl
     *   The local url for the image
     * @param bool $absolute
     *   If true absolute path
     *
     * @return array<string>
     */
    public function getDerived(string $imageUrl, bool $absolute = true): array;
}
