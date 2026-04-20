<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Controller\Admin\EventCrudController;
use App\Controller\Admin\MyEventCrudController;
use App\DataFixtures\OrganizationFixtures;
use App\Tests\Fixtures\TestUserFixtures;
use App\Tests\Functional\AbstractAdminTestCase;

final class DashboardTest extends AbstractAdminTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures([
            OrganizationFixtures::class,
            TestUserFixtures::class,
        ]);
    }

    public function testSuperAdminSeesAllMenuSections(): void
    {
        $this->loginAs(TestUserFixtures::SUPER_ADMIN_EMAIL);
        $this->client->request('GET', '/admin');

        if ($this->client->getResponse()->isRedirection()) {
            $this->client->followRedirect();
        }

        $this->assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();
        $this->assertStringContainsString('admin.label.all_content', $content);
        $this->assertStringContainsString('admin.label.users', $content);
        $this->assertStringContainsString('admin.label.feeds', $content);
    }

    public function testEditorRedirectsToEventCrud(): void
    {
        $this->loginAs(TestUserFixtures::EDITOR_EMAIL);
        $this->client->request('GET', '/admin');

        $this->assertResponseRedirects();
        $location = (string) $this->client->getResponse()->headers->get('Location');
        $decoded = rawurldecode($location);
        $this->assertTrue(
            str_contains($decoded, EventCrudController::class) || str_contains($decoded, '/admin/event'),
            sprintf('Expected redirect to EventCrudController, got %s', $location),
        );
    }

    public function testOrgEditorRedirectsToMyEventCrud(): void
    {
        $this->loginAs(TestUserFixtures::ORG_EDITOR_A_EMAIL);
        $this->client->request('GET', '/admin');

        $this->assertResponseRedirects();
        $location = (string) $this->client->getResponse()->headers->get('Location');
        $decoded = rawurldecode($location);
        $this->assertTrue(
            str_contains($decoded, MyEventCrudController::class) || str_contains($decoded, '/admin/my-event'),
            sprintf('Expected redirect to MyEventCrudController, got %s', $location),
        );
    }

    public function testOrgEditorDoesNotSeeAdminMenuItems(): void
    {
        $this->loginAs(TestUserFixtures::ORG_EDITOR_A_EMAIL);
        $this->client->request('GET', '/admin');

        if ($this->client->getResponse()->isRedirection()) {
            $this->client->followRedirect();
        }

        $content = (string) $this->client->getResponse()->getContent();
        $this->assertStringNotContainsString('admin.label.users', $content);
        $this->assertStringNotContainsString('admin.label.feeds', $content);
    }
}
