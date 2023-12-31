<?php

namespace App\Factory;

use App\Entity\Tag;
use App\Repository\TagRepository;

final class TagsFactory
{
    public function __construct(
        private readonly TagRepository $tagRepository
    ) {
    }

    /**
     * Create tag or find matching in the database.
     *
     * @param array<string> $tagNames
     *   The tag names to create/lockup in the database as strings
     *
     * @return iterable<Tag>
     *   Yield tag entities from the database
     */
    public function createOrLookup(array $tagNames): iterable
    {
        foreach ($tagNames as $tagName) {
            $tag = $this->tagRepository->findOneBy(['name' => $tagName]);
            if (is_null($tag)) {
                $tag = new Tag();
                $tag->setName($tagName);
                $this->tagRepository->save($tag);
            }

            yield $tag;
        }
    }
}
