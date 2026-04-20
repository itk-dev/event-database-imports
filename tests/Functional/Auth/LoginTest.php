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

    public function testLoginPageRenders(): void
    {
        $crawler = $this->client->request('GET', '/admin/login');

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter('form[action="/admin/login"]'));
        $this->assertCount(1, $crawler->filter('input[name="_username"]'));
        $this->assertCount(1, $crawler->filter('input[name="_password"]'));
        $this->assertCount(1, $crawler->filter('input[name="_csrf_token"]'));
    }

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

    public function testCsrfTokenIsRequired(): void
    {
        $this->client->request('POST', '/admin/login', [
            '_username' => TestUserFixtures::EDITOR_EMAIL,
            '_password' => TestUserFixtures::PASSWORD,
        ]);

        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-danger');
    }
}
