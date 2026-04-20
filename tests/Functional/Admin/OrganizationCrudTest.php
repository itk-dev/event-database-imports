<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Controller\Admin\OrganizationCrudController;
use App\DataFixtures\OrganizationFixtures;
use App\Entity\Organization;
use App\Tests\Fixtures\TestUserFixtures;
use App\Tests\Functional\AbstractAdminTestCase;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;

final class OrganizationCrudTest extends AbstractAdminTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures([
            OrganizationFixtures::class,
            TestUserFixtures::class,
        ]);
    }

    public function testAnonymousAccessRedirects(): void
    {
        $this->client->request('GET', $this->adminUrl(OrganizationCrudController::class));

        $this->assertResponseRedirects();
    }

    public function testIndexLoadsForEditor(): void
    {
        $this->loginAs(TestUserFixtures::EDITOR_EMAIL);
        $this->client->request('GET', $this->adminUrl(OrganizationCrudController::class));

        $this->assertResponseIsSuccessful();
    }

    public function testDetailLoadsOnExistingRow(): void
    {
        $this->loginAs(TestUserFixtures::EDITOR_EMAIL);
        $org = $this->findOrganizationByName('Aakb');

        $this->client->request('GET', $this->adminUrl(OrganizationCrudController::class, Action::DETAIL, ['entityId' => $org->getId()]));

        $this->assertResponseIsSuccessful();
    }

    public function testEditorCanAccessNewForm(): void
    {
        $this->loginAs(TestUserFixtures::EDITOR_EMAIL);
        $this->client->request('GET', $this->adminUrl(OrganizationCrudController::class, Action::NEW));

        $this->assertResponseIsSuccessful();
    }

    public function testOrgAdminCannotCreateOrganization(): void
    {
        $this->loginAs(TestUserFixtures::ORG_ADMIN_A_EMAIL);
        $this->client->request('GET', $this->adminUrl(OrganizationCrudController::class, Action::NEW));

        $status = $this->client->getResponse()->getStatusCode();
        $this->assertContains($status, [302, 403], sprintf('Expected 302 or 403, got %d', $status));
    }

    public function testOrgAdminCannotEditOtherOrganization(): void
    {
        $this->loginAs(TestUserFixtures::ORG_ADMIN_A_EMAIL);
        $otherOrg = $this->findOrganizationByName('Dokk1');

        $this->client->request('GET', $this->adminUrl(OrganizationCrudController::class, Action::EDIT, ['entityId' => $otherOrg->getId()]));

        $status = $this->client->getResponse()->getStatusCode();
        $this->assertContains($status, [302, 403], sprintf('Expected 302 or 403, got %d', $status));
    }

    private function findOrganizationByName(string $name): Organization
    {
        $repository = static::getContainer()->get('doctrine')->getManager()->getRepository(Organization::class);
        $org = $repository->findOneBy(['name' => $name]);
        $this->assertInstanceOf(Organization::class, $org, sprintf('Organization "%s" not found', $name));

        return $org;
    }
}
