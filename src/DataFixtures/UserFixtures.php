<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserFixtures extends Fixture
{
    public const ADMIN_USER = 'admin';
    public const USER = 'user';

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setName('admin')
            ->setMail('admin@itkdev.dk')
            ->setUpdatedBy('admin')
            ->setRoles(['ROLE_ADMIN', 'ROLE_USER'])
            ->setEnabled(true);
        $manager->persist($user);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'admin'));
        $this->addReference(self::ADMIN_USER, $user);

        $user = new User();
        $user->setName('Test Testersen')
            ->setMail('tester@itkdev.dk')
            ->setUpdatedBy('admin')
            ->setRoles(['ROLE_ADMIN'])
            ->setEnabled(true);
        $manager->persist($user);
        $user->setPassword($this->passwordHasher->hashPassword($user, '1233456789'));
        $this->addReference(self::USER, $user);

        // Make it stick.
        $manager->flush();
    }
}
