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

    /**
     * Verifies the organization index loads successfully for an editor.
     */
    public function testIndexLoadsForEditor(): void
    {
        $this->loginAs(TestUserFixtures::EDITOR_EMAIL);
        $this->client->request('GET', $this->adminUrl(OrganizationCrudController::class));

        $this->assertResponseIsSuccessful();
    }

    /**
     * Verifies the detail page loads for an existing organization.
     */
    public function testDetailLoadsOnExistingRow(): void
    {
        $this->loginAs(TestUserFixtures::EDITOR_EMAIL);
        $org = $this->findOrganizationByName('Aakb');

        $this->client->request('GET', $this->adminUrl(OrganizationCrudController::class, Action::DETAIL, ['entityId' => $org->getId()]));

        $this->assertResponseIsSuccessful();
    }

    /**
     * Verifies an editor can access the new organization form.
     */
    public function testEditorCanAccessNewForm(): void
    {
        $this->loginAs(TestUserFixtures::EDITOR_EMAIL);
        $this->client->request('GET', $this->adminUrl(OrganizationCrudController::class, Action::NEW));

        $this->assertResponseIsSuccessful();
    }

    /**
     * An organization admin (non-editor) is denied URL-level access to the NEW form.
     */
    public function testOrgAdminCannotCreateOrganization(): void
    {
        $this->loginAs(TestUserFixtures::ORG_ADMIN_A_EMAIL);
        $this->client->request('GET', $this->adminUrl(OrganizationCrudController::class, Action::NEW));

        $status = $this->client->getResponse()->getStatusCode();
        $this->assertContains($status, [302, 403], sprintf('Expected 302 or 403 for NEW, got %d', $status));
    }

    /**
     * Verifies an org admin is denied access to edit another organization.
     */
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
