<?php

namespace App\Service;

use Symfony\Component\String\Slugger\AsciiSlugger;

class Slugger
{
    public static function slugify(?string $name): ?string
    {
        return null === $name ? null : (new AsciiSlugger())->slug($name)->lower()->toString();
    }
}
