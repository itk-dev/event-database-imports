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
    public const DOKK1 = 'dokk1';

    public function load(ObjectManager $manager): void
    {
        $this->createOrganization(
            $manager,
            'ITKDev',
            'info@itkdev.dk',
            'https://github.com/itk-dev',
            self::ITK
        );
        $this->createOrganization(
            $manager,
            'Aakb',
            'info@aakb.dk',
            'https://aakb.dk/',
            self::AAKB
        );
        $this->createOrganization(
            $manager,
            'Dokk1',
            'info@dokk1.dk',
            'https://dokk1.dk/',
            self::DOKK1
        );

        // Make it stick.
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }

    /**
     * Create an organization.
     *
     * @param ObjectManager $manager
     *   The instance of ObjectManager
     * @param string $name
     *   The name of the organization
     * @param string $mail
     *   The email address of the organization
     * @param string $url
     *   The website URL of the organization
     * @param string $reference
     *   The reference name for the organization
     */
    private function createOrganization(ObjectManager $manager, string $name, string $mail, string $url, string $reference): void
    {
        $org = new Organization();
        $org->setName($name)
            ->setMail($mail)
            ->setUrl($url)
            ->addUser($this->getReference(UserFixtures::USER));
        $manager->persist($org);
        $this->addReference($reference, $org);
    }
}
