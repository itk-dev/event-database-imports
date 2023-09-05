<?php

namespace App\DataFixtures;

use App\Entity\Organization;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class OrganizationFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $org = new Organization();
        $org->setName('ITKDev')
            ->setMail('info@itkdev.dk')
            ->setUrl('https://github.com/itk-dev')
            ->addUser($this->getReference(UserFixtures::USER));
        $manager->persist($org);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
