<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Controller\Admin\EventCrudController;
use App\DataFixtures\OrganizationFixtures;
use App\Entity\Event;
use App\Tests\Fixtures\TestEventFixtures;
use App\Tests\Fixtures\TestUserFixtures;
use App\Tests\Functional\AbstractAdminTestCase;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use PHPUnit\Framework\Attributes\DataProvider;

final class EventCrudTest extends AbstractAdminTestCase
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

    #[DataProvider('authorizedRoleProvider')]
    public function testIndexLoadsForAuthorizedRole(string $email): void
    {
        $this->loginAs($email);
        $this->client->request('GET', $this->adminUrl(EventCrudController::class));

        $this->assertResponseIsSuccessful();
    }

    /**
     * @return iterable<array{string}>
     */
    public static function authorizedRoleProvider(): iterable
    {
        yield 'super admin' => [TestUserFixtures::SUPER_ADMIN_EMAIL];
        yield 'admin' => [TestUserFixtures::ADMIN_EMAIL];
        yield 'editor' => [TestUserFixtures::EDITOR_EMAIL];
        yield 'org editor' => [TestUserFixtures::ORG_EDITOR_A_EMAIL];
    }

    public function testDetailLoadsOnExistingRow(): void
    {
        $this->loginAs(TestUserFixtures::EDITOR_EMAIL);
        $event = $this->findEventByTitle('Org A Event 1');

        $this->client->request('GET', $this->adminUrl(EventCrudController::class, Action::DETAIL, ['entityId' => $event->getId()]));

        $this->assertResponseIsSuccessful();
    }

    public function testEditorCanAccessNewForm(): void
    {
        $this->loginAs(TestUserFixtures::EDITOR_EMAIL);
        $this->client->request('GET', $this->adminUrl(EventCrudController::class, Action::NEW));

        $this->assertResponseIsSuccessful();
    }

    public function testOrgEditorCannotEditOtherOrgEvent(): void
    {
        $this->loginAs(TestUserFixtures::ORG_EDITOR_A_EMAIL);
        $event = $this->findEventByTitle('Org B Event 1');

        $this->client->request('GET', $this->adminUrl(EventCrudController::class, Action::EDIT, ['entityId' => $event->getId()]));

        $status = $this->client->getResponse()->getStatusCode();
        $this->assertContains($status, [302, 403], sprintf('Expected 302 or 403, got %d', $status));
    }

    private function findEventByTitle(string $title): Event
    {
        $repository = static::getContainer()->get('doctrine')->getManager()->getRepository(Event::class);
        $event = $repository->findOneBy(['title' => $title]);
        $this->assertInstanceOf(Event::class, $event, sprintf('Event "%s" not found', $title));

        return $event;
    }
}
