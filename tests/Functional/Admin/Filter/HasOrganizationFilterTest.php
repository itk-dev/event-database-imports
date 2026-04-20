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

    public function testFilterForEventsWithoutOrganization(): void
    {
        $this->loginAs(TestUserFixtures::EDITOR_EMAIL);
        $this->client->request('GET', $this->adminUrl(EventCrudController::class, 'index', [
            'filters[hasOrganization][comparison]' => '=',
            'filters[hasOrganization][value]' => '0',
        ]));

        $this->assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();
        $this->assertStringContainsString('Orphan Event', $content);
        $this->assertStringNotContainsString('Org A Event 1', $content);
    }

    public function testFilterForEventsWithOrganization(): void
    {
        $this->loginAs(TestUserFixtures::EDITOR_EMAIL);
        $this->client->request('GET', $this->adminUrl(EventCrudController::class, 'index', [
            'filters[hasOrganization][comparison]' => '=',
            'filters[hasOrganization][value]' => '1',
        ]));

        $this->assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();
        $this->assertStringContainsString('Org A Event 1', $content);
        $this->assertStringNotContainsString('Orphan Event', $content);
    }
}
