<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class TagsFixtures extends Fixture implements DependentFixtureInterface
{
    public const CONCERT = 'tags_concert';
    public const KIDS = 'tags_kids';
    public const RACE = 'tags_race';
    public const AROS = 'tags_aros';
    public const ITKDEV = 'tags_itkdev';

    public function load(ObjectManager $manager): void
    {
        $tag = new Tag();
        $tag->setName('aros')
            ->addVocabulary($this->getReference(VocabularyFixtures::MANAGED));
        $manager->persist($tag);
        $this->addReference(self::AROS, $tag);

        $tag = new Tag();
        $tag->setName('theoceanraceaarhus')
            ->addVocabulary($this->getReference(VocabularyFixtures::MANAGED));
        $manager->persist($tag);
        $this->addReference(self::RACE, $tag);

        $tag = new Tag();
        $tag->setName('For børn')
            ->addVocabulary($this->getReference(VocabularyFixtures::MANAGED));
        $manager->persist($tag);
        $this->addReference(self::KIDS, $tag);

        $tag = new Tag();
        $tag->setName('Koncert')
            ->addVocabulary($this->getReference(VocabularyFixtures::MANAGED));
        $manager->persist($tag);
        $this->addReference(self::CONCERT, $tag);

        $tag = new Tag();
        $tag->setName('ITKDev')
            ->addVocabulary($this->getReference(VocabularyFixtures::MANAGED))
            ->setEditable(true);
        $manager->persist($tag);
        $this->addReference(self::ITKDEV, $tag);


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
