<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class TagsFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $tag = new Tag();
        $tag->setName('aros')
            ->addVocabulary($this->getReference(VocabularyFixtures::MANAGED));
        $manager->persist($tag);

        $tag = new Tag();
        $tag->setName('theoceanraceaarhus')
            ->addVocabulary($this->getReference(VocabularyFixtures::MANAGED));
        $manager->persist($tag);

        $tag = new Tag();
        $tag->setName('For børn')
            ->addVocabulary($this->getReference(VocabularyFixtures::MANAGED));
        $manager->persist($tag);

        $tag = new Tag();
        $tag->setName('Koncert')
            ->addVocabulary($this->getReference(VocabularyFixtures::MANAGED));
        $manager->persist($tag);

        // Make it stick.
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            VocabularyFixtures::class,
        ];
    }
}
