<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin\Filter;

use App\Controller\Admin\EventCrudController;
use App\DataFixtures\OrganizationFixtures;
use App\Tests\Fixtures\TestEventFixtures;
use App\Tests\Fixtures\TestUserFixtures;
use App\Tests\Functional\AbstractAdminTestCase;

final class HasOrganizationFilterTest extends AbstractAdminTestCase
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
     * Verifies the hasOrganization=0 filter shows only events without an organization.
     */
    public function testFilterForEventsWithoutOrganization(): void
    {
        $this->loginAs(TestUserFixtures::EDITOR_EMAIL);
        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, $this->adminUrl(EventCrudController::class, 'index', [
            'filters[hasOrganization]' => '0',
        ]));

        $this->assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();
        $this->assertStringContainsString('Orphan Event', $content);
        $this->assertStringNotContainsString('Org A Event 1', $content);
    }

    /**
     * Verifies the hasOrganization=1 filter shows only events with an organization.
     */
    public function testFilterForEventsWithOrganization(): void
    {
        $this->loginAs(TestUserFixtures::EDITOR_EMAIL);
        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, $this->adminUrl(EventCrudController::class, 'index', [
            'filters[hasOrganization]' => '1',
        ]));

        $this->assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();
        $this->assertStringContainsString('Org A Event 1', $content);
        $this->assertStringNotContainsString('Orphan Event', $content);
    }
}
