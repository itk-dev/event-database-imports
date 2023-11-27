<?php

namespace App\DataFixtures;

use App\Entity\Image;
use App\Service\ImageHandlerInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class ImagesFixtures extends Fixture
{
    public const AAK = 'image_aak';
    public const ITK = 'image_itk';

    public function __construct(
        private readonly ImageHandlerInterface $imageHandler,
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        $image = new Image();
        $image->setEditable(true)
            ->setTitle('ITK Test image')
            ->setSource('https://itk.aarhus.dk/media/79711/itk-4f-10.png');
        $image->setLocal($this->imageHandler->fetch($image->getSource()));
        $manager->persist($image);
        $this->addReference(self::ITK, $image);

        $image = new Image();
        $image->setEditable(false)
            ->setTitle('AAK Test image')
            ->setSource('https://placehold.co/600x400/0FF0FF/FF0000.png?text=AAK-Test');
        $image->setLocal($this->imageHandler->fetch($image->getSource()));
        $manager->persist($image);
        $this->addReference(self::AAK, $image);

        // Make it stick.
        $manager->flush();
    }
}
