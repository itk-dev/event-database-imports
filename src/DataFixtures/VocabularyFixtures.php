<?php

namespace App\DataFixtures;

use App\Entity\Vocabulary;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class VocabularyFixtures extends Fixture
{
    public const VOCAB_MANAGED = 'vocab_managed';
    public const VOCAB_FREE = 'vocab_free';

    public function load(ObjectManager $manager): void
    {
        $vocab = new Vocabulary();
        $vocab->setName('managed')
            ->setDescription('Managed tags vocabulary');
        $manager->persist($vocab);
        $manager->flush();
        $this->addReference(self::VOCAB_MANAGED, $vocab);

        $vocab_free = new Vocabulary();
        $vocab_free->setName('feeds')
            ->setDescription('Free tags from feeds');
        $manager->persist($vocab_free);
        $manager->flush();
        $this->addReference(self::VOCAB_FREE, $vocab_free);
    }
}
