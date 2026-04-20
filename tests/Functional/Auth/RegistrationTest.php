<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth;

use App\DataFixtures\OrganizationFixtures;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Fixtures\TestUserFixtures;
use App\Tests\Functional\AbstractAdminTestCase;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

final class RegistrationTest extends AbstractAdminTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures([
            OrganizationFixtures::class,
            TestUserFixtures::class,
        ]);
    }

    public function testRegistrationPageRenders(): void
    {
        $crawler = $this->client->request('GET', '/admin/register/');

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter('input[name="registration_form[name]"]'));
        $this->assertCount(1, $crawler->filter('input[name="registration_form[mail]"]'));
        $this->assertCount(1, $crawler->filter('input[name="registration_form[plainPassword]"]'));
    }

    public function testSuccessfulRegistrationPersistsUserAndSendsEmail(): void
    {
        $crawler = $this->client->request('GET', '/admin/register/');
        $form = $crawler->selectButton('registration.page.btn.register')->form();

        $email = 'new-user@test';
        $this->client->submit($form, [
            'registration_form[name]' => 'New User',
            'registration_form[mail]' => $email,
            'registration_form[registrationNotes]' => 'Please approve',
            'registration_form[plainPassword]' => 'secret-password',
            'registration_form[agreeTerms]' => '1',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertEmailCount(1);

        $repository = static::getContainer()->get(UserRepository::class);
        $user = $repository->findOneBy(['mail' => $email]);
        $this->assertNotNull($user);
        $this->assertNull($user->getEmailVerifiedAt());
    }

    public function testEmailVerificationSetsVerifiedAt(): void
    {
        $crawler = $this->client->request('GET', '/admin/register/');
        $form = $crawler->selectButton('registration.page.btn.register')->form();

        $email = 'verify-me@test';
        $this->client->submit($form, [
            'registration_form[name]' => 'Verify Me',
            'registration_form[mail]' => $email,
            'registration_form[registrationNotes]' => 'Please approve',
            'registration_form[plainPassword]' => 'secret-password',
            'registration_form[agreeTerms]' => '1',
        ]);

        $repository = static::getContainer()->get(UserRepository::class);
        $user = $repository->findOneBy(['mail' => $email]);
        $this->assertInstanceOf(User::class, $user);

        $helper = static::getContainer()->get(VerifyEmailHelperInterface::class);
        $signature = $helper->generateSignature(
            'app_verify_email',
            (string) $user->getId(),
            $user->getMail(),
            ['id' => $user->getId()],
        );

        $path = parse_url($signature->getSignedUrl(), PHP_URL_PATH).'?'.parse_url($signature->getSignedUrl(), PHP_URL_QUERY);
        $this->client->request('GET', $path);

        $this->assertResponseRedirects();
        static::getContainer()->get('doctrine')->getManager()->refresh($user);
        $this->assertNotNull($user->getEmailVerifiedAt());
    }
}
