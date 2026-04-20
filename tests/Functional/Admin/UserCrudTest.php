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

    public function testAdminCanAccessIndex(): void
    {
        $this->loginAs(TestUserFixtures::ADMIN_EMAIL);
        $this->client->request('GET', $this->adminUrl(UserCrudController::class));

        $this->assertResponseIsSuccessful();
    }

    public function testAdminCanCreateNewUser(): void
    {
        $this->loginAs(TestUserFixtures::ADMIN_EMAIL);
        $this->client->request('GET', $this->adminUrl(UserCrudController::class, Action::NEW));

        $this->assertResponseIsSuccessful();
    }

    public function testOrgEditorCannotAccessNewUser(): void
    {
        $this->loginAs(TestUserFixtures::ORG_EDITOR_A_EMAIL);
        $this->client->request('GET', $this->adminUrl(UserCrudController::class, Action::NEW));

        $status = $this->client->getResponse()->getStatusCode();
        $this->assertContains($status, [302, 403], sprintf('Expected 302 or 403, got %d', $status));
    }

    public function testUserCanEditOwnProfile(): void
    {
        $this->loginAs(TestUserFixtures::ORG_EDITOR_A_EMAIL);
        $self = $this->findUser(TestUserFixtures::ORG_EDITOR_A_EMAIL);

        $this->client->request('GET', $this->adminUrl(UserCrudController::class, Action::EDIT, ['entityId' => $self->getId()]));

        $this->assertResponseIsSuccessful();
    }
}
