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
        $this->createUser(
            $manager,
            'admin',
            'admin@itkdev.dk',
            ['ROLE_ADMIN', 'ROLE_USER'],
            'admin',
            self::ADMIN_USER
        );

        $this->createUser(
            $manager,
            'Test Testersen',
            'tester@itkdev.dk',
            ['ROLE_ADMIN'],
            '1233456789',
            self::USER
        );

        // Make it stick.
        $manager->flush();
    }

    /**
     * Creates a new user and saves it to the database.
     *
     * @param objectManager $manager
     *   The object manager used to persist the user
     * @param string $name
     *   The name of the user
     * @param string $email
     *   The email of the user
     * @param array $roles
     *   The roles assigned to the user
     * @param string $password
     *   The password of the user
     * @param string $reference
     *   The reference name used to add a reference to the user
     */
    private function createUser(ObjectManager $manager, string $name, string $email, array $roles, string $password, string $reference): void
    {
        $user = new User();
        $user->setName($name)
            ->setMail($email)
            ->setUpdatedBy('admin')
            ->setRoles($roles)
            ->setEnabled(true)
            ->setPassword($this->passwordHasher->hashPassword($user, $password));

        $manager->persist($user);
        $this->addReference($reference, $user);
    }
}
