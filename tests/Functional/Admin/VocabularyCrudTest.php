<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Controller\Admin\VocabularyCrudController;
use App\DataFixtures\OrganizationFixtures;
use App\Tests\Fixtures\TestUserFixtures;
use App\Tests\Functional\AbstractAdminTestCase;

final class VocabularyCrudTest extends AbstractAdminTestCase
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
        $this->client->request('GET', $this->adminUrl(VocabularyCrudController::class));

        $this->assertResponseIsSuccessful();
    }

    public function testEditorCannotAccessIndex(): void
    {
        $this->loginAs(TestUserFixtures::EDITOR_EMAIL);
        $this->client->request('GET', $this->adminUrl(VocabularyCrudController::class));

        $status = $this->client->getResponse()->getStatusCode();
        $this->assertContains($status, [302, 403], sprintf('Expected 302 or 403, got %d', $status));
    }
}
