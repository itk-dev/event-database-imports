<?php

namespace App\DataFixtures;

use App\Entity\Image;
use App\Service\ImageServiceInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class ImagesFixtures extends Fixture
{
    public const AAK = 'image_aak';
    public const ITK = 'image_itk';

    public function __construct(
        private readonly ImageServiceInterface $imageHandler,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $this->prepareAndPersistImage(
            $manager,
            'ITK Test image',
            'https://placehold.co/600x400/0FF4FF/FF0440.png?text=ITKDev-Test',
            true,
            self::ITK
        );

        $this->prepareAndPersistImage(
            $manager,
            'AAK Test image',
            'https://placehold.co/600x400/0FF0FF/FF0000.png?text=AAK-Test',
            false,
            self::AAK
        );

        $manager->flush();
    }

    /**
     * Prepare and persist an Image object.
     *
     * @param ObjectManager $manager
     *   The ObjectManager instance
     * @param string $title
     *   The title of the image
     * @param string $source
     *   The source of the image
     * @param bool $editable
     *   Whether the image is editable or not
     * @param string $reference
     *   The reference for the image
     */
    private function prepareAndPersistImage(ObjectManager $manager, string $title, string $source, bool $editable, string $reference): void
    {
        $image = new Image();

        $image->setEditable($editable)
            ->setTitle($title)
            ->setSource($source)
            ->setLocal($this->imageHandler->fetch($source));

        $manager->persist($image);
        $this->addReference($reference, $image);
    }
}
