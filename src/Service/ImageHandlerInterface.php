<?php

namespace App\Service;

use App\Entity\Image;

interface ImageHandlerInterface
{
    public function fetch(string $url): string;

    public function remove(Image $image): bool;

    public function transform(Image $image): void;
}
