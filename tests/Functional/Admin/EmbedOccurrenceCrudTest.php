<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Controller\Admin\EmbedOccurrenceCrudController;
use App\DataFixtures\OrganizationFixtures;
use App\Tests\Fixtures\TestUserFixtures;
use App\Tests\Functional\AbstractAdminTestCase;

final class EmbedOccurrenceCrudTest extends AbstractAdminTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures([
            OrganizationFixtures::class,
            TestUserFixtures::class,
        ]);
    }

    public function testIndexRendersForAuthorizedUser(): void
    {
        $this->loginAs(TestUserFixtures::EDITOR_EMAIL);
        $this->client->request('GET', $this->adminUrl(EmbedOccurrenceCrudController::class));

        $status = $this->client->getResponse()->getStatusCode();
        $this->assertContains($status, [200, 302], sprintf('Expected 200 or 302, got %d', $status));
    }
}
