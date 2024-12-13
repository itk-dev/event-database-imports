<?php

namespace App\DataFixtures;

use App\Entity\Location;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class LocationFixture extends Fixture implements DependentFixtureInterface
{
    public const ITKDEV = 'location-itkdev';

    public function load(ObjectManager $manager): void
    {
        $location = new Location();
        $location->setName('ITK Development')
            ->setMail('itkdev@mkb.aarhus.dk')
            ->setUrl('https://itk.aarhus.dk/om-itk/afdelinger/development/')
            ->setAddress($this->getReference(AddressFixture::ITKDEV))
            ->setDisabilityAccess(true)
            ->setEditable(true);
        $manager->persist($location);
        $this->addReference(self::ITKDEV, $location);

        // Make it stick.
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AddressFixture::class,
        ];
    }
}
