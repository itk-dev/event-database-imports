<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin\Filter;

use App\Controller\Admin\MyEventCrudController;
use App\DataFixtures\OrganizationFixtures;
use App\Tests\Fixtures\TestEventFixtures;
use App\Tests\Fixtures\TestUserFixtures;
use App\Tests\Functional\AbstractAdminTestCase;

final class MyEventScopingTest extends AbstractAdminTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures([
            OrganizationFixtures::class,
            TestUserFixtures::class,
            TestEventFixtures::class,
        ]);
    }

    /**
     * Verifies an organization editor's event list is scoped to their own organization.
     */
    public function testOrgEditorOnlySeesOwnOrgEvents(): void
    {
        $this->loginAs(TestUserFixtures::ORG_EDITOR_A_EMAIL);
        $this->client->request('GET', $this->adminUrl(MyEventCrudController::class));

        $this->assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();
        $this->assertStringContainsString('Org A Event 1', $content);
        $this->assertStringContainsString('Org A Event 2', $content);
        $this->assertStringNotContainsString('Org B Event 1', $content);
        $this->assertStringNotContainsString('Orphan Event', $content);
    }

    /**
     * Verifies scoping applies per organization editor, not just a fixed organization.
     */
    public function testOtherOrgEditorSeesOtherOrgEvents(): void
    {
        $this->loginAs(TestUserFixtures::ORG_EDITOR_B_EMAIL);
        $this->client->request('GET', $this->adminUrl(MyEventCrudController::class));

        $this->assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();
        $this->assertStringContainsString('Org B Event 1', $content);
        $this->assertStringNotContainsString('Org A Event 1', $content);
    }
}
