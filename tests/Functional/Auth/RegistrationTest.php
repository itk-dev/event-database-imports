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

    /**
     * Verifies registration persists an unverified user and queues a verification email.
     */
    public function testSuccessfulRegistrationPersistsUserAndSendsEmail(): void
    {
        $crawler = $this->client->request('GET', '/admin/register/');
        $form = $crawler->selectButton('Registrer dig')->form();

        $email = 'new-user@example.com';
        $this->client->enableProfiler();
        $this->client->submit($form, [
            'registration_form[name]' => 'New User',
            'registration_form[mail]' => $email,
            'registration_form[registrationNotes]' => 'Please approve',
            'registration_form[plainPassword]' => 'secret-password',
            'registration_form[agreeTerms]' => '1',
        ]);

        $this->assertResponseIsSuccessful();

        $repository = static::getContainer()->get(UserRepository::class);
        $user = $repository->findOneBy(['mail' => $email]);
        $this->assertInstanceOf(User::class, $user, 'User should have been persisted during registration');
        $this->assertNull($user->getEmailVerifiedAt());

        // Outbound mail goes through the async Messenger transport in tests,
        // so the message is queued (routed via SendEmailMessage) rather than
        // immediately dispatched to the mailer transport.
        $this->assertQueuedEmailCount(1);
    }

    /**
     * Verifies visiting the signed verification link sets the user's verified-at timestamp.
     */
    public function testEmailVerificationSetsVerifiedAt(): void
    {
        $crawler = $this->client->request('GET', '/admin/register/');
        $form = $crawler->selectButton('Registrer dig')->form();

        $email = 'verify-me@example.com';
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
        // Re-fetch rather than refresh: the test container's EM may have
        // been reset between the registration and verification requests.
        $verifiedUser = $repository->findOneBy(['mail' => $email]);
        $this->assertInstanceOf(User::class, $verifiedUser);
        $this->assertNotNull($verifiedUser->getEmailVerifiedAt());
    }
}
