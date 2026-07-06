<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Controller\Admin\UserCrudController;
use App\DataFixtures\OrganizationFixtures;
use App\Tests\Fixtures\TestUserFixtures;
use App\Tests\Functional\AbstractAdminTestCase;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;

final class UserCrudTest extends AbstractAdminTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures([
            OrganizationFixtures::class,
            TestUserFixtures::class,
        ]);
    }

    /**
     * Verifies an admin can access the user index.
     */
    public function testAdminCanAccessIndex(): void
    {
        $this->loginAs(TestUserFixtures::ADMIN_EMAIL);
        $this->client->request('GET', $this->adminUrl(UserCrudController::class));

        $this->assertResponseIsSuccessful();
    }

    /**
     * Verifies an admin can access the new user form.
     */
    public function testAdminCanCreateNewUser(): void
    {
        $this->loginAs(TestUserFixtures::ADMIN_EMAIL);
        $this->client->request('GET', $this->adminUrl(UserCrudController::class, Action::NEW));

        $this->assertResponseIsSuccessful();
    }

    /**
     * Verifies an org editor is denied access to the new user form.
     */
    public function testOrgEditorCannotAccessNewUser(): void
    {
        $this->loginAs(TestUserFixtures::ORG_EDITOR_A_EMAIL);
        $this->client->request('GET', $this->adminUrl(UserCrudController::class, Action::NEW));

        $status = $this->client->getResponse()->getStatusCode();
        $this->assertContains($status, [302, 403], sprintf('Expected 302 or 403, got %d', $status));
    }

    /**
     * Verifies a user can edit their own profile.
     */
    public function testUserCanEditOwnProfile(): void
    {
        $this->loginAs(TestUserFixtures::ORG_EDITOR_A_EMAIL);
        $self = $this->findUser(TestUserFixtures::ORG_EDITOR_A_EMAIL);

        $this->client->request('GET', $this->adminUrl(UserCrudController::class, Action::EDIT, ['entityId' => $self->getId()]));

        $this->assertResponseIsSuccessful();
    }
}
