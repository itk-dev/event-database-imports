<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

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

    /**
     * Verifies the super admin dashboard renders all menu sections.
     */
    public function testSuperAdminSeesAllMenuSections(): void
    {
        $this->loginAs(TestUserFixtures::SUPER_ADMIN_EMAIL);
        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, '/admin');

        if ($this->client->getResponse()->isRedirection()) {
            $this->client->followRedirect();
        }

        $this->assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();
        // Menu items are rendered using the default (da) locale, so assert on
        // the translated text rather than the translation key.
        $this->assertStringContainsString('Alt indhold', $content);
        $this->assertStringContainsString('Brugere', $content);
        $this->assertStringContainsString('Feeds', $content);
    }

    /**
     * Verifies editors are redirected from the dashboard to the event CRUD.
     */
    public function testEditorRedirectsToEventCrud(): void
    {
        $this->loginAs(TestUserFixtures::EDITOR_EMAIL);
        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, '/admin');

        $this->assertResponseRedirects();
        $path = (string) parse_url((string) $this->client->getResponse()->headers->get('Location'), PHP_URL_PATH);
        $this->assertSame('/admin/event', $path);
    }

    /**
     * Verifies org editors are redirected from the dashboard to their "my event" CRUD.
     */
    public function testOrgEditorRedirectsToMyEventCrud(): void
    {
        $this->loginAs(TestUserFixtures::ORG_EDITOR_A_EMAIL);
        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, '/admin');

        $this->assertResponseRedirects();
        $path = (string) parse_url((string) $this->client->getResponse()->headers->get('Location'), PHP_URL_PATH);
        $this->assertSame('/admin/my-event', $path);
    }

    /**
     * Verifies org editors do not see admin-only menu items.
     */
    public function testOrgEditorDoesNotSeeAdminMenuItems(): void
    {
        $this->loginAs(TestUserFixtures::ORG_EDITOR_A_EMAIL);
        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, '/admin');

        if ($this->client->getResponse()->isRedirection()) {
            $this->client->followRedirect();
        }

        $content = (string) $this->client->getResponse()->getContent();
        // Menu items use the default (da) locale - assert on translated text.
        $this->assertStringNotContainsString('Brugere', $content);
        $this->assertStringNotContainsString('Feeds', $content);
    }
}
