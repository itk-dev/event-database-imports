<?php

declare(strict_types=1);

namespace App\Tests\Fixtures;

use App\DataFixtures\OrganizationFixtures;
use App\Entity\Organization;
use App\Entity\User;
use App\Types\UserRoles;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class TestUserFixtures extends Fixture implements DependentFixtureInterface
{
    public const PASSWORD = 'test-password';

    public const SUPER_ADMIN_EMAIL = 'super-admin@test';
    public const ADMIN_EMAIL = 'admin@test';
    public const EDITOR_EMAIL = 'editor@test';
    public const ORG_ADMIN_A_EMAIL = 'org-admin-a@test';
    public const ORG_EDITOR_A_EMAIL = 'org-editor-a@test';
    public const ORG_EDITOR_B_EMAIL = 'org-editor-b@test';

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $orgA = $this->getReference(OrganizationFixtures::AAKB, Organization::class);
        $orgB = $this->getReference(OrganizationFixtures::DOKK1, Organization::class);

        $this->createUser($manager, 'Super Admin', self::SUPER_ADMIN_EMAIL, [UserRoles::ROLE_SUPER_ADMIN->value]);
        $this->createUser($manager, 'Admin', self::ADMIN_EMAIL, [UserRoles::ROLE_ADMIN->value]);
        $this->createUser($manager, 'Editor', self::EDITOR_EMAIL, [UserRoles::ROLE_EDITOR->value]);
        $this->createUser($manager, 'Org Admin A', self::ORG_ADMIN_A_EMAIL, [UserRoles::ROLE_ORGANIZATION_ADMIN->value], [$orgA]);
        $this->createUser($manager, 'Org Editor A', self::ORG_EDITOR_A_EMAIL, [UserRoles::ROLE_ORGANIZATION_EDITOR->value], [$orgA]);
        $this->createUser($manager, 'Org Editor B', self::ORG_EDITOR_B_EMAIL, [UserRoles::ROLE_ORGANIZATION_EDITOR->value], [$orgB]);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [OrganizationFixtures::class];
    }

    /**
     * @param list<string>        $roles
     * @param list<Organization>  $organizations
     */
    private function createUser(ObjectManager $manager, string $name, string $email, array $roles, array $organizations = []): void
    {
        $user = new User();
        $user->setName($name)
            ->setMail($email)
            ->setUpdatedBy('test')
            ->setRoles($roles)
            ->setEnabled(true)
            ->setPassword($this->passwordHasher->hashPassword($user, self::PASSWORD));

        foreach ($organizations as $org) {
            $user->addOrganization($org);
        }

        $manager->persist($user);
    }
}
