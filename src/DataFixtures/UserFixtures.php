<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public const ADMIN_USER = 'admin';
    public const USER = 'user';

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setName('admin')
            ->setMail('admin@itkdev.dk')
            // Password: admin
            // @todo: Use PasswordAuthenticatedUserInterface when users are correctly implemented.
            ->setPassword('$2y$13$QFJIcHB.G8kPqvDfDwbozOPjGMtHtHXN7gcVZCO43EwD3RaZHQRtW')
            ->setUpdatedBy('admin')
            ->setEnabled(true);
        $manager->persist($user);
        $this->addReference(self::ADMIN_USER, $user);

        $user = new User();
        $user->setName('Test Testersen')
            ->setMail('tester@itkdev.dk')
            // Password: 1233456789
            // @todo: Use PasswordAuthenticatedUserInterface when users are correctly implemented.
            ->setPassword('$2y$13$ptcIsrnZSXwjNcEj6RZUIOkotUy/j2amaKAIYViUABaJdNl6Y4WIa')
            ->setUpdatedBy('admin')
            ->setEnabled(true);
        $manager->persist($user);
        $manager->flush();
        $this->addReference(self::USER, $user);

        // Make it stick.
        $manager->flush();
    }
}
