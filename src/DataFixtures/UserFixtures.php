<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public const ADMIN_USER_REFERENCE = 'admin-user';
    public const USER_REFERENCE = 'user';

    /**
     * @{@inheritdoc}
     */
    public function load(ObjectManager $manager): void
    {
        $userAdmin = new User();
        $userAdmin->setName('admin')
            ->setMail('admin@itkdev.dk')
            // Password: admin
            ->setPassword('$2y$13$QFJIcHB.G8kPqvDfDwbozOPjGMtHtHXN7gcVZCO43EwD3RaZHQRtW')
            ->setUpdatedBy('admin')
            ->setEnabled(true);
        $manager->persist($userAdmin);
        $manager->flush();
        $this->addReference(self::ADMIN_USER_REFERENCE, $userAdmin);

        $user = new User();
        $user->setName('Test Testersen')
            ->setMail('tester@itkdev.dk')
            // Password: 1233456789
            ->setPassword('$2y$13$ptcIsrnZSXwjNcEj6RZUIOkotUy/j2amaKAIYViUABaJdNl6Y4WIa')
            ->setUpdatedBy('admin')
            ->setEnabled(true);
        $manager->persist($user);
        $manager->flush();
        $this->addReference(self::USER_REFERENCE, $user);
    }
}
