<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth;

use App\DataFixtures\OrganizationFixtures;
use App\Tests\Fixtures\TestUserFixtures;
use App\Tests\Functional\AbstractAdminTestCase;

final class AuthorizationTest extends AbstractAdminTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures([
            OrganizationFixtures::class,
            TestUserFixtures::class,
        ]);
    }

    public function testAnonymousUserRedirectedToLogin(): void
    {
        $this->client->request('GET', '/admin');

        $this->assertResponseRedirects();
        $this->assertStringContainsString('/admin/login', (string) $this->client->getResponse()->headers->get('Location'));
    }

    public function testAuthenticatedUserCanAccessAdmin(): void
    {
        $this->loginAs(TestUserFixtures::EDITOR_EMAIL);
        $this->client->request('GET', '/admin');

        // Expect either direct 200 or a redirect to an admin controller page
        $status = $this->client->getResponse()->getStatusCode();
        $this->assertTrue(
            in_array($status, [200, 302], true),
            sprintf('Expected 200 or 302, got %d', $status),
        );

        if (302 === $status) {
            $this->assertStringStartsWith('/admin', (string) $this->client->getResponse()->headers->get('Location'));
        }
    }
}
