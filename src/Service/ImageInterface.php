<?php

namespace App\Service;

interface ImageInterface
{
    public function fetch(string $url): string;

    public function remove(\App\Entity\Image $image): bool;

    public function transform(\App\Entity\Image $image): bool;
}
