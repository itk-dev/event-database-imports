<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Controller\Admin\EventCrudController;
use App\Controller\Admin\FeedCrudController;
use App\DataFixtures\OrganizationFixtures;
use App\Entity\Event;
use App\Entity\Feed;
use App\Entity\Organization;
use App\Tests\Fixtures\TestUserFixtures;
use App\Tests\Functional\AbstractAdminTestCase;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;

/**
 * Characterizes action-level authorization that only exists as UI/HTTP behavior
 * today: Feed mutations are super-admin only, and feed-imported events are
 * read-only (ADR 007). The feed-event guard is unit-tested on EventVoter, but
 * not asserted through the actual admin URL — this pins that behavior before
 * the EasyAdmin 5 upgrade, which touches how permissions gate actions.
 */
final class AdminActionAuthorizationTest extends AbstractAdminTestCase
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
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $this->assertInstanceOf(EntityManagerInterface::class, $em);

        return $em;
    }

    private function createFeed(): Feed
    {
        $feed = new Feed();
        $feed->setName('Characterization Feed')
            ->setEnabled(true)
            ->setConfiguration([]);
        $this->entityManager()->persist($feed);

        return $feed;
    }

    /**
     * A plain admin (not super-admin) cannot reach the Feed edit action.
     */
    public function testAdminCannotEditFeed(): void
    {
        $feed = $this->createFeed();
        $this->entityManager()->flush();
        $id = $feed->getId();
        $this->entityManager()->clear();

        $this->loginAs(TestUserFixtures::ADMIN_EMAIL);
        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, $this->adminUrl(FeedCrudController::class, Action::EDIT, ['entityId' => $id]));

        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * A super-admin can reach the Feed edit action (the counterpart to the above).
     */
    public function testSuperAdminCanEditFeed(): void
    {
        $feed = $this->createFeed();
        $this->entityManager()->flush();
        $id = $feed->getId();
        $this->entityManager()->clear();

        $this->loginAs(TestUserFixtures::SUPER_ADMIN_EMAIL);
        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, $this->adminUrl(FeedCrudController::class, Action::EDIT, ['entityId' => $id]));

        $this->assertResponseIsSuccessful();
    }

    /**
     * A feed-imported event is read-only: even an admin is denied the edit
     * action (ADR 007, enforced by EventVoter's feed guard).
     */
    public function testFeedImportedEventEditIsDenied(): void
    {
        $org = $this->entityManager()->getRepository(Organization::class)->findOneBy([]);
        $this->assertInstanceOf(Organization::class, $org);

        $feed = $this->createFeed();

        $event = new Event();
        $event->setTitle('Imported event')
            ->setDescription('desc')
            ->setUpdatedBy('feed')
            ->setEditable(false)
            ->setOrganization($org)
            ->setFeed($feed);
        $this->entityManager()->persist($event);
        $this->entityManager()->flush();
        $id = $event->getId();
        $this->entityManager()->clear();

        $this->loginAs(TestUserFixtures::ADMIN_EMAIL);
        $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, $this->adminUrl(EventCrudController::class, Action::EDIT, ['entityId' => $id]));

        $this->assertResponseStatusCodeSame(403);
    }
}
