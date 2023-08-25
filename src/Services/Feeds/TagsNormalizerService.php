<?php

namespace App\Services\Feeds;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManager;

class TagsNormalizerService implements TagsNormalizerInterface
{
    public function __construct(
        private readonly EntityManager $em,
        private readonly TagRepository $tagRepository,
    ) {
    }

    /**
     * Normalize list of names to ensure they fit the DB schema.
     *
     * @param array $names
     *   Array with tags names
     *
     * @return array array<strings>
     *   Normalized array with tag names
     */
    public function normalize(array $names): array
    {
        if (!empty($names)) {
            $names = $this->trimLength($names);
            $names = $this->normalizeToDbName($names);
        }

        return $names;
    }

    /**
     * Trim names in list to ensure they fit the DB schema.
     *
     * @param array $names
     *   Array with tags names
     *
     * @return array
     *   Normalized array with tag names
     */
    private function trimLength(array $names): array
    {
        $metadata = $this->em->getClassMetadata(Tag::class);
        $maxNameLength = isset($metadata->fieldMappings, $metadata->fieldMappings['name'], $metadata->fieldMappings['name']['length']) ? (int) $metadata->fieldMappings['name']['length'] : 50;

        // Ensure we don't exceed field length in db
        return array_map(function ($name) use ($maxNameLength) {
            return mb_substr(trim($name), 0, $maxNameLength);
        }, $names);
    }

    /**
     * Normalize to the database tag name.
     *
     * @todo: Make it possible to limit normalization against single controlled vocabulary.
     *
     * @param array $names
     *   Array with tags names
     *
     * @return array
     *   Normalized array with tag names
     */
    private function normalizeToDbName(array $names): array
    {
        $normalizedNames = [];
        foreach ($names as $name) {
            $tag = $this->tagRepository->findOneBy(['name' => $name]);
            if ($tag) {
                $normalizedNames[] = $tag->getName();
            } else {
                $normalizedNames[] = $name;
            }
        }

        return $normalizedNames;
    }
}
