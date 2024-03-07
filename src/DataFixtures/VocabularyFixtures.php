<?php

namespace App\DataFixtures;

use App\Entity\Vocabulary;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class VocabularyFixtures extends Fixture
{
    public const MANAGED = 'managed';
    public const FREE = 'free';

    public function load(ObjectManager $manager): void
    {
        $this->createAndSaveVocabulary($manager, 'aarhusguiden.dk', 'Managed tags vocabulary for aarhusguiden.dk', self::MANAGED);
        $this->createAndSaveVocabulary($manager, 'feeds', 'Free tags from feeds', self::FREE);

        // Make it stick.
        $manager->flush();
    }

    /**
     * Creates and saves a new vocabulary.
     *
     * @param ObjectManager $manager
     *   The object manager responsible for persisting the vocabulary
     * @param string $name
     *   The name of the vocabulary
     * @param string $description
     *   The description of the vocabulary
     */
    private function createAndSaveVocabulary(ObjectManager $manager, string $name, string $description, string $reference): void
    {
        $vocabulary = new Vocabulary();
        $vocabulary->setName($name)
            ->setDescription($description);

        $manager->persist($vocabulary);
        $this->addReference($reference, $vocabulary);
    }
}
