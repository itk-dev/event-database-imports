<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth;

use App\DataFixtures\OrganizationFixtures;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Fixtures\TestUserFixtures;
use App\Tests\Functional\AbstractAdminTestCase;
use App\Types\UserRoles;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Characterizes the post-login gates enforced by
 * {@see \App\Security\EventListener\LoginSuccessListener}: unaccepted terms and
 * unverified email both redirect away from the admin. These fire on a real form
 * login (not on the programmatic loginUser() used by the CRUD tests), so they
 * are exercised here by submitting the login form.
 *
 * This is a regression net for the EasyAdmin 4 -> 5 upgrade: the admin is only
 * reachable through these gates.
 */
final class LoginGateTest extends AbstractAdminTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures([
            OrganizationFixtures::class,
            TestUserFixtures::class,
        ]);
    }

    private function entityManager(): EntityManagerInterface
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);
        self::assertInstanceOf(EntityManagerInterface::class, $em);

        return $em;
    }

    private function submitLogin(string $email, string $password = TestUserFixtures::PASSWORD): void
    {
        $crawler = $this->client->request('GET', '/admin/login');
        $form = $crawler->filter('form')->form();

        $this->client->submit($form, [
            '_username' => $email,
            '_password' => $password,
        ]);
    }

    /**
     * A user who has not accepted the terms is redirected to the accept-terms
     * page after login (editor role => the email-verified gate is skipped).
     */
    public function testUnacceptedTermsRedirectsToAcceptTerms(): void
    {
        // Editor fixtures have termsAcceptedAt = null by default.
        $this->submitLogin(TestUserFixtures::EDITOR_EMAIL);

        $this->assertResponseRedirects();
        self::assertStringContainsString(
            '/admin/accept-terms',
            (string) $this->client->getResponse()->headers->get('Location'),
        );
    }

    /**
     * Accepting the terms persists the timestamp and lets the user into the admin.
     */
    public function testAcceptingTermsPersistsTimestampAndUnblocks(): void
    {
        $this->submitLogin(TestUserFixtures::EDITOR_EMAIL);
        $crawler = $this->client->followRedirect(); // accept-terms page

        $form = $crawler->filter('form')->form();
        $this->client->submit($form, [
            'accept_terms_form[agreeTerms]' => '1',
        ]);

        $this->assertResponseRedirects();
        self::assertStringContainsString('/admin', (string) $this->client->getResponse()->headers->get('Location'));
        self::assertStringNotContainsString('/accept-terms', (string) $this->client->getResponse()->headers->get('Location'));

        $this->entityManager()->clear();
        $editor = static::getContainer()->get(UserRepository::class)->findOneBy(['mail' => TestUserFixtures::EDITOR_EMAIL]);
        self::assertInstanceOf(User::class, $editor);
        self::assertNotNull($editor->getTermsAcceptedAt(), 'Accepting the terms must persist termsAcceptedAt');
    }

    /**
     * A user who has already accepted the terms reaches the admin directly.
     */
    public function testUserWithAcceptedTermsIsNotGated(): void
    {
        $em = $this->entityManager();
        $editor = static::getContainer()->get(UserRepository::class)->findOneBy(['mail' => TestUserFixtures::EDITOR_EMAIL]);
        self::assertInstanceOf(User::class, $editor);
        $editor->setTermsAcceptedAt(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));
        $em->flush();

        $this->submitLogin(TestUserFixtures::EDITOR_EMAIL);

        $this->assertResponseRedirects();
        self::assertStringNotContainsString('/accept-terms', (string) $this->client->getResponse()->headers->get('Location'));
    }

    /**
     * A non-privileged user (below ORGANIZATION_EDITOR) with an unverified email
     * is bounced back to the login page with an error, even after accepting terms.
     */
    public function testUnverifiedPlainUserIsBouncedToLogin(): void
    {
        $em = $this->entityManager();
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        self::assertInstanceOf(UserPasswordHasherInterface::class, $hasher);

        $user = new User();
        $user->setName('Plain User')
            ->setMail('plain-user@test')
            ->setRoles([UserRoles::ROLE_USER->value])
            ->setEnabled(true)
            ->setUpdatedBy('test')
            ->setTermsAcceptedAt(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));
        $user->setPassword($hasher->hashPassword($user, TestUserFixtures::PASSWORD));
        // emailVerifiedAt intentionally left null.
        $em->persist($user);
        $em->flush();

        $this->submitLogin('plain-user@test');

        $this->assertResponseRedirects();
        self::assertStringContainsString('/admin/login', (string) $this->client->getResponse()->headers->get('Location'));
    }
}
