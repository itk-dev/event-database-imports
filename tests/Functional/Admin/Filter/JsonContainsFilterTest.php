<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin\Filter;

use App\Controller\Admin\UserCrudController;
use App\DataFixtures\OrganizationFixtures;
use App\Tests\Fixtures\TestUserFixtures;
use App\Tests\Functional\AbstractAdminTestCase;
use App\Types\UserRoles;

final class JsonContainsFilterTest extends AbstractAdminTestCase
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
     * Verifies filtering users by editor role excludes users with other roles.
     */
    public function testFilterByEditorRole(): void
    {
        $this->loginAs(TestUserFixtures::ADMIN_EMAIL);
        $this->client->request('GET', $this->adminUrl(UserCrudController::class, 'index', [
            'filters[roles][comparison]' => '=',
            'filters[roles][value]' => UserRoles::ROLE_EDITOR->value,
        ]));

        $this->assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();
        $this->assertStringContainsString(TestUserFixtures::EDITOR_EMAIL, $content);
        $this->assertStringNotContainsString(TestUserFixtures::ORG_EDITOR_A_EMAIL, $content);
    }

    /**
     * Verifies filtering matches users sharing a role stored in a JSON column.
     */
    public function testFilterByOrgEditorRole(): void
    {
        $this->loginAs(TestUserFixtures::ADMIN_EMAIL);
        $this->client->request('GET', $this->adminUrl(UserCrudController::class, 'index', [
            'filters[roles][comparison]' => '=',
            'filters[roles][value]' => UserRoles::ROLE_ORGANIZATION_EDITOR->value,
        ]));

        $this->assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();
        $this->assertStringContainsString(TestUserFixtures::ORG_EDITOR_A_EMAIL, $content);
        $this->assertStringContainsString(TestUserFixtures::ORG_EDITOR_B_EMAIL, $content);
        $this->assertStringNotContainsString(TestUserFixtures::EDITOR_EMAIL, $content);
    }
}
