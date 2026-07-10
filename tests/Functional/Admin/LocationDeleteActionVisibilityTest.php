<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Controller\Admin\LocationCrudController;
use App\DataFixtures\OrganizationFixtures;
use App\Entity\Address;
use App\Entity\Event;
use App\Entity\Location;
use App\Entity\Organization;
use App\Tests\Fixtures\TestUserFixtures;
use App\Tests\Functional\AbstractAdminTestCase;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use Symfony\Component\HttpFoundation\Request;

/**
 * The Delete action must be hidden for a location that is still in use (has
 * events): LocationVoter denies delete for used locations, and EasyAdmin
 * consults the voter when building actions, so the button is not rendered.
 * This pins the end-to-end behavior the voter guard exists to produce.
 */
final class LocationDeleteActionVisibilityTest extends AbstractAdminTestCase
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

    private function createLocation(string $name): Location
    {
        $address = new Address();
        $address->setStreet($name.' Street 1')
            ->setCity('Aarhus')
            ->setCountry('DK')
            ->setPostalCode('8000')
            ->setRegion('Midtjylland')
            ->setEditable(true);
        $this->entityManager()->persist($address);

        $location = new Location();
        $location->setName($name)
            ->setAddress($address);
        $this->entityManager()->persist($location);

        return $location;
    }

    /**
     * A used location (attached to an event) offers no Delete action, while an
     * otherwise-identical unused location does — proving the voter's "unused"
     * guard reaches EasyAdmin's action rendering.
     */
    public function testDeleteHiddenForUsedLocationButShownForUnused(): void
    {
        $org = $this->entityManager()->getRepository(Organization::class)->findOneBy([]);
        $this->assertInstanceOf(Organization::class, $org);

        $unused = $this->createLocation('Unused location');
        $used = $this->createLocation('Used location');

        $event = new Event();
        $event->setTitle('Event at used location')
            ->setDescription('desc')
            ->setUpdatedBy('test')
            ->setEditable(true)
            ->setOrganization($org)
            ->setLocation($used);
        $this->entityManager()->persist($event);
        $this->entityManager()->flush();

        $unusedId = $unused->getId();
        $usedId = $used->getId();
        $this->entityManager()->clear();

        $this->loginAs(TestUserFixtures::EDITOR_EMAIL);

        $unusedCrawler = $this->client->request(Request::METHOD_GET, $this->adminUrl(LocationCrudController::class, Action::DETAIL, ['entityId' => $unusedId]));
        $this->assertResponseIsSuccessful();
        $this->assertGreaterThan(0, $unusedCrawler->filter('.action-delete')->count(), 'Unused location should offer the Delete action.');

        $usedCrawler = $this->client->request(Request::METHOD_GET, $this->adminUrl(LocationCrudController::class, Action::DETAIL, ['entityId' => $usedId]));
        $this->assertResponseIsSuccessful();
        $this->assertCount(0, $usedCrawler->filter('.action-delete'), 'Used location must not offer the Delete action.');
    }
}
