<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth;

use App\DataFixtures\OrganizationFixtures;
use App\Tests\Fixtures\TestUserFixtures;
use App\Tests\Functional\AbstractAdminTestCase;

final class LoginTest extends AbstractAdminTestCase
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
     * Verifies a successful login redirects the user to the admin area.
     */
    public function testValidCredentialsRedirectToAdmin(): void
    {
        $crawler = $this->client->request('GET', '/admin/login');
        $form = $crawler->filter('form')->form();

        $this->client->submit($form, [
            '_username' => TestUserFixtures::EDITOR_EMAIL,
            '_password' => TestUserFixtures::PASSWORD,
        ]);

        $this->assertResponseRedirects();
        $this->assertStringStartsWith('/admin', (string) $this->client->getResponse()->headers->get('Location'));
    }

    /**
     * Verifies an invalid password shows an error message instead of logging in.
     */
    public function testInvalidCredentialsShowError(): void
    {
        $crawler = $this->client->request('GET', '/admin/login');
        $form = $crawler->filter('form')->form();

        $this->client->submit($form, [
            '_username' => TestUserFixtures::EDITOR_EMAIL,
            '_password' => 'wrong-password',
        ]);

        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-danger');
    }
}
