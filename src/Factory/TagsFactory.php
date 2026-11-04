<?php

namespace App\Factory;

use App\Entity\Tag;
use App\Entity\Vocabulary;
use App\Repository\TagRepository;
use App\Service\Slugger;

final readonly class TagsFactory
{
    public function __construct(
        private TagRepository $tagRepository,
    ) {
    }

    /**
     * Create tag or find matching in the database.
     *
     * @param array<string> $tagNames
     *   The tags to create/lockup in the database as strings
     *
     * @return iterable<Tag>
     *   Yield tag entities from the database
     */
    public function createOrLookup(array $tagNames, ?Vocabulary $vocabulary = null): iterable
    {
        // Normalize to lowercase
        $tagNames = array_map(mb_strtolower(...), $tagNames);
        // Ensure we don't have duplicates
        $tagNames = array_flip($tagNames);

        foreach (array_keys($tagNames) as $tagName) {
            $tag = $this->tagRepository->findOneBy(['slug' => Slugger::slugify($tagName)]);
            if (is_null($tag)) {
                $tag = new Tag();
                $tag->setName($tagName);
            }

            if (!is_null($vocabulary)) {
                $tag->addVocabulary($vocabulary);
            }

            $this->tagRepository->save($tag);

            yield $tag;
        }
    }
}
