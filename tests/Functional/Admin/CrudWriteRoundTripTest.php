<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Controller\Admin\EventCrudController;
use App\Controller\Admin\TagCrudController;
use App\DataFixtures\OrganizationFixtures;
use App\Entity\Event;
use App\Entity\Occurrence;
use App\Entity\Tag;
use App\Message\ImageMessage;
use App\MessageHandler\ImageHandler;
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
     * Clearing the (optional) URL of an event saves it as null instead of failing with a 500.
     */
    public function testClearEventUrlViaEditForm(): void
    {
        $event = $this->entityManager()->getRepository(Event::class)->findOneBy(['title' => 'Org A Event 1']);
        $this->assertInstanceOf(Event::class, $event);
        $event->setUrl('https://example.com/event');
        $this->entityManager()->flush();
        $id = $event->getId();
        $this->entityManager()->clear();

        $this->loginAs(TestUserFixtures::ADMIN_EMAIL);

        $crawler = $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, $this->adminUrl(EventCrudController::class, Action::EDIT, ['entityId' => $id]));
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name="Event"]')->form();
        $this->client->submit($form, ['Event[url]' => '']);

        $this->assertResponseRedirects();

        $this->entityManager()->clear();
        $updated = $this->entityManager()->getRepository(Event::class)->find($id);
        $this->assertInstanceOf(Event::class, $updated);
        $this->assertNull($updated->getUrl());
    }

    /**
     * The full life of an admin image: upload it, let the async import handle it, edit the event without touching
     * the image, replace the image, and delete it.
     *
     * Regression: the import rewrote the stored path to "/uploads/<file>", which EasyAdmin's upload field rejects,
     * so the next edit of the event cleared the image (prod, after 1.3.0).
     */
    public function testAdminImageLifecycle(): void
    {
        $imageDir = self::getContainer()->getParameter('kernel.project_dir').'/public/images/';
        $source = tempnam(sys_get_temp_dir(), 'upload').'.png';
        imagepng(imagecreatetruecolor(10, 10), $source);
        $files = [];

        try {
            $event = $this->entityManager()->getRepository(Event::class)->findOneBy(['title' => 'Org A Event 1']);
            $this->assertInstanceOf(Event::class, $event);
            $id = $event->getId();
            $this->assertNotNull($id);

            $this->loginAs(TestUserFixtures::ADMIN_EMAIL);

            // 1. Upload an image through the admin.
            $form = $this->editForm($id);
            $form['Event[image][title]'] = 'Test image';
            $form['Event[image][local][file]']->upload($source);
            $this->client->submit($form);
            $this->assertResponseRedirects();

            $local = $this->findEvent($id)->getImage()?->getLocal();
            $this->assertIsString($local);
            $files[] = $local;
            $this->assertMatchesRegularExpression('#^uploads/[^/]+\.png$#', $local, 'An admin upload is stored as "uploads/<file>"');
            $this->assertFileExists($imageDir.$local);
            $this->assertStringEndsWith('/images/'.$local, (string) $this->findEvent($id)->getImage()?->getSource());

            // 2. The async import step (ImageMessage) must keep the stored value.
            $imageId = $this->findEvent($id)->getImage()?->getId();
            $handler = self::getContainer()->get(ImageHandler::class);
            $this->assertInstanceOf(ImageHandler::class, $handler);
            $handler(new ImageMessage($id, $imageId));
            $this->assertSame($local, $this->findEvent($id)->getImage()?->getLocal());

            // 3. Edit the event without touching the image: the image is kept.
            $this->client->submit($this->editForm($id), ['Event[title]' => 'Org A Event 1 (edited)']);
            $this->assertResponseRedirects();

            $image = $this->findEvent($id)->getImage();
            $this->assertSame($local, $image?->getLocal());
            $this->assertStringEndsWith('/images/'.$local, (string) $image->getSource());
            $this->assertFileExists($imageDir.$local);

            // 4. Replace the image: the new file is stored and the old file is deleted.
            $form = $this->editForm($id);
            $form['Event[image][local][file]']->upload($source);
            $this->client->submit($form);
            $this->assertResponseRedirects();

            $replaced = $this->findEvent($id)->getImage()?->getLocal();
            $this->assertIsString($replaced);
            $files[] = $replaced;
            $this->assertNotSame($local, $replaced);
            $this->assertMatchesRegularExpression('#^uploads/[^/]+\.png$#', $replaced);
            $this->assertFileExists($imageDir.$replaced);
            $this->assertFileDoesNotExist($imageDir.$local);

            // 5. Delete the image: the file is deleted and nothing is stored.
            $form = $this->editForm($id);
            $form['Event[image][local][delete]']->tick();
            $this->client->submit($form);
            $this->assertResponseRedirects();

            $this->assertNull($this->findEvent($id)->getImage()?->getLocal());
            $this->assertFileDoesNotExist($imageDir.$replaced);
        } finally {
            @unlink($source);
            foreach ($files as $file) {
                @unlink($imageDir.$file);
            }
        }
    }

    private function editForm(int $id): \Symfony\Component\DomCrawler\Form
    {
        $crawler = $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, $this->adminUrl(EventCrudController::class, Action::EDIT, ['entityId' => $id]));
        $this->assertResponseIsSuccessful();

        return $crawler->filter('form[name="Event"]')->form();
    }

    private function findEvent(int $id): Event
    {
        $this->entityManager()->clear();
        $event = $this->entityManager()->getRepository(Event::class)->find($id);
        $this->assertInstanceOf(Event::class, $event);

        return $event;
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
