<?php

namespace App\DataFixtures;

use App\Entity\Address;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class AddressFixture extends Fixture
{
    public const ITKDEV = 'address-itkdev';

    public function load(ObjectManager $manager): void
    {
        $address = new Address();
        $address->setStreet('Hack Kampmanns Plads 2')
            ->setSuite('2.2')
            ->setRegion('Jylland')
            ->setPostalCode('8000')
            ->setCountry('Danmark')
            ->setCity('Aarhus')
            ->setLatitude(56.1507645)
            ->setLongitude(10.2112699);
        $manager->persist($address);
        $this->addReference(self::ITKDEV, $address);

        // Make it stick.
        $manager->flush();
    }
}
