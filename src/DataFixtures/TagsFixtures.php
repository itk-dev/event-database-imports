<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TagsFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $tag1 = new Tag();
        $tag1->setName('aros')
            ->addVocabulary($this->getReference(VocabularyFixtures::VOCAB_MANAGED));
        $manager->persist($tag1);
        $manager->flush();

        $tag2 = new Tag();
        $tag2->setName('theoceanraceaarhus')
            ->addVocabulary($this->getReference(VocabularyFixtures::VOCAB_MANAGED));
        $manager->persist($tag2);
        $manager->flush();

        $tag3 = new Tag();
        $tag3->setName('For børn')
            ->addVocabulary($this->getReference(VocabularyFixtures::VOCAB_MANAGED));
        $manager->persist($tag3);
        $manager->flush();

        $tag4 = new Tag();
        $tag4->setName('Koncert')
            ->addVocabulary($this->getReference(VocabularyFixtures::VOCAB_MANAGED));
        $manager->persist($tag4);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            VocabularyFixtures::class,
        ];
    }
}
