<?php

namespace App\Services;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;

final class TagsNormalizer implements TagsNormalizerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
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
            $names = array_filter($names);
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
        $maxNameLength = (int) ($metadata->fieldMappings['name']['length'] ?? 50);

        // Ensure we don't exceed field length in db
        return array_map(
            static fn (string $name) => mb_substr(trim($name), 0, $maxNameLength),
            $names
        );
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
            $normalizedNames[] = $tag ? $tag->getName() : $name;
        }

        return $normalizedNames;
    }
}
