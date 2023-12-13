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
        $this->createTag($manager, 'aros', self::AROS);
        $this->createTag($manager, 'theoceanraceaarhus', self::RACE);
        $this->createTag($manager, 'For børn', self::KIDS);
        $this->createTag($manager, 'Koncert', self::CONCERT);
        $this->createTag($manager, 'ITKDev', self::ITKDEV, true);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            VocabularyFixtures::class,
        ];
    }

    /**
     * Create a new tag.
     *
     * @param objectManager $manager
     *   The object manager instance
     * @param string $name
     *   The name of the tag
     * @param string $reference
     *   The reference for the tag
     * @param bool $editable (optional)
     *   Whether the tag is editable (default: false)
     */
    private function createTag(ObjectManager $manager, string $name, string $reference, bool $editable = false): void
    {
        $tag = new Tag();
        $tag->setName($name)
            ->addVocabulary($this->getReference(VocabularyFixtures::MANAGED))
            ->setEditable($editable);

        $manager->persist($tag);
        $this->addReference($reference, $tag);
    }
}
