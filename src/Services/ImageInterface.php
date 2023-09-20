<?php

namespace App\Services;

interface ImageInterface
{
    public function fetch(string $url): string;

    public function remove(\App\Entity\Image $image): bool;

    public function transform(\App\Entity\Image $image): bool;
}
