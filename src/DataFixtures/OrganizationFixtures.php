<?php

namespace App\DataFixtures;

use App\Entity\Organization;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class OrganizationFixtures extends Fixture implements DependentFixtureInterface
{
    public const ITK = 'itk';
    public const AAKB = 'aakb';

    public function load(ObjectManager $manager): void
    {
        $org = new Organization();
        $org->setName('ITKDev')
            ->setMail('info@itkdev.dk')
            ->setUrl('https://github.com/itk-dev')
            ->addUser($this->getReference(UserFixtures::USER));
        $manager->persist($org);
        $this->addReference(self::ITK, $org);

        $org = new Organization();
        $org->setName('Aakb')
            ->setMail('info@aakb.dk.dk')
            ->setUrl('https://aakb.dk/')
            ->addUser($this->getReference(UserFixtures::USER));
        $manager->persist($org);
        $this->addReference(self::AAKB, $org);

        // Make it stick.
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
