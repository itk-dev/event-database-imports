<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Controller\Admin\EventCrudController;
use App\Controller\Admin\TagCrudController;
use App\DataFixtures\OrganizationFixtures;
use App\Entity\Event;
use App\Entity\Occurrence;
use App\Entity\Tag;
use App\Tests\Fixtures\TestEventFixtures;
use App\Tests\Fixtures\TestUserFixtures;
use App\Tests\Functional\AbstractAdminTestCase;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;

/**
 * Characterizes the EasyAdmin write path end-to-end: creating and editing
 * entities by submitting the generated forms, plus the occurrence cascade that
 * the delete action relies on. Existing admin tests only assert GET access, so
 * this pins the form-handling / persistence behavior before the EasyAdmin 5
 * upgrade (which changes form rendering and field configurators).
 *
 * Delete is driven by EasyAdmin's JavaScript confirmation modal and cannot be
 * submitted headlessly via BrowserKit, so the delete-cascade behavior is
 * characterized at the ORM level instead.
 */
final class CrudWriteRoundTripTest extends AbstractAdminTestCase
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

    private function entityManager(): EntityManagerInterface
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $this->assertInstanceOf(EntityManagerInterface::class, $em);

        return $em;
    }

    /**
     * Submitting the "new" form creates the entity.
     */
    public function testCreateTagViaNewForm(): void
    {
        $this->loginAs(TestUserFixtures::ADMIN_EMAIL);

        $crawler = $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, $this->adminUrl(TagCrudController::class, Action::NEW));
        $this->assertResponseIsSuccessful();

        // The slug field is disabled and auto-generated from the name via a
        // PrePersist hook, so only the name is submitted.
        $form = $crawler->filter('form[name="Tag"]')->form();
        $this->client->submit($form, [
            'Tag[name]' => 'Characterization Tag',
        ]);

        $this->assertResponseRedirects();

        $this->entityManager()->clear();
        $tag = $this->entityManager()->getRepository(Tag::class)->findOneBy(['name' => 'Characterization Tag']);
        $this->assertInstanceOf(Tag::class, $tag);
        $this->assertNotEmpty($tag->getSlug(), 'The slug must be auto-generated on persist');
    }

    /**
     * Submitting the "edit" form updates the entity.
     */
    public function testEditTagViaEditForm(): void
    {
        $tag = new Tag();
        $tag->setName('Before')->setSlug(); // slug derived from name
        $this->entityManager()->persist($tag);
        $this->entityManager()->flush();
        $id = $tag->getId();
        // Detach so the edit request re-hydrates from the DB (as a real request
        // would), rather than reusing the in-memory Gedmo-stamped timestamp.
        $this->entityManager()->clear();

        $this->loginAs(TestUserFixtures::ADMIN_EMAIL);

        $crawler = $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, $this->adminUrl(TagCrudController::class, Action::EDIT, ['entityId' => $id]));
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="Tag"]')->form();
        $this->client->submit($form, ['Tag[name]' => 'After']);

        $this->assertResponseRedirects();

        $this->entityManager()->clear();
        $updated = $this->entityManager()->getRepository(Tag::class)->find($id);
        $this->assertInstanceOf(Tag::class, $updated);
        $this->assertSame('After', $updated->getName());
    }

    /**
     * Editing an event through the form persists the change and stamps the
     * acting user via the blame mechanism.
     */
    public function testEditEventTitleViaEditFormStampsUpdatedBy(): void
    {
        $event = $this->entityManager()->getRepository(Event::class)->findOneBy(['title' => 'Org A Event 1']);
        $this->assertInstanceOf(Event::class, $event);
        $id = $event->getId();

        $this->loginAs(TestUserFixtures::ADMIN_EMAIL);

        $crawler = $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, $this->adminUrl(EventCrudController::class, Action::EDIT, ['entityId' => $id]));
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="Event"]')->form();
        $this->client->submit($form, ['Event[title]' => 'Org A Event 1 (edited)']);

        $this->assertResponseRedirects();

        $this->entityManager()->clear();
        $updated = $this->entityManager()->getRepository(Event::class)->find($id);
        $this->assertInstanceOf(Event::class, $updated);
        $this->assertSame('Org A Event 1 (edited)', $updated->getTitle());
        $this->assertSame(TestUserFixtures::ADMIN_EMAIL, $updated->getUpdatedBy());
    }

    /**
     * Removing an event cascades to its occurrences — the data-integrity
     * behavior the admin delete action depends on.
     */
    public function testDeletingEventRemovesItsOccurrences(): void
    {
        $em = $this->entityManager();
        $org = $this->entityManager()->getRepository(\App\Entity\Organization::class)->findOneBy([]);
        $this->assertInstanceOf(\App\Entity\Organization::class, $org);

        $event = new Event();
        $event->setTitle('Cascade Event')
            ->setDescription('desc')
            ->setUpdatedBy('test')
            ->setEditable(true)
            ->setOrganization($org);

        $occurrence = new Occurrence();
        $occurrence->setStart(new \DateTimeImmutable('2026-07-01 10:00:00', new \DateTimeZone('UTC')))
            ->setEnd(new \DateTimeImmutable('2026-07-01 12:00:00', new \DateTimeZone('UTC')))
            ->setEvent($event);
        $event->addOccurrence($occurrence);

        $em->persist($event);
        $em->persist($occurrence);
        $em->flush();

        $eventId = $event->getId();
        $occurrenceId = $occurrence->getId();
        $this->assertNotNull($occurrenceId);

        $em->remove($event);
        $em->flush();
        $em->clear();

        $this->assertNotInstanceOf(Event::class, $em->getRepository(Event::class)->find($eventId));
        $this->assertNotInstanceOf(Occurrence::class, $em->getRepository(Occurrence::class)->find($occurrenceId), 'Deleting an event must remove its occurrences');
    }
}
