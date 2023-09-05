<?php

namespace App\DataFixtures;

use App\Entity\Vocabulary;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class VocabularyFixtures extends Fixture
{
    public const MANAGED = 'managed';
    public const FREE = 'free';

    public function load(ObjectManager $manager): void
    {
        $vocabulary = new Vocabulary();
        $vocabulary->setName('managed')
            ->setDescription('Managed tags vocabulary');
        $manager->persist($vocabulary);
        $this->addReference(self::MANAGED, $vocabulary);

        $vocabulary = new Vocabulary();
        $vocabulary->setName('feeds')
            ->setDescription('Free tags from feeds');
        $manager->persist($vocabulary);
        $this->addReference(self::FREE, $vocabulary);

        // Make it stick.
        $manager->flush();
    }
}
