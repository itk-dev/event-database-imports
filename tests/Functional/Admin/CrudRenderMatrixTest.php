<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Controller\Admin\AddressCrudController;
use App\Controller\Admin\EmbedOccurrenceCrudController;
use App\Controller\Admin\EventCrudController;
use App\Controller\Admin\FeedCrudController;
use App\Controller\Admin\LocationCrudController;
use App\Controller\Admin\OrganizationCrudController;
use App\Controller\Admin\TagCrudController;
use App\Controller\Admin\UserCrudController;
use App\Controller\Admin\VocabularyCrudController;
use App\DataFixtures\AddressFixture;
use App\DataFixtures\DailyOccurrenceFixture;
use App\DataFixtures\EventFixture;
use App\DataFixtures\FeedFixtures;
use App\DataFixtures\LocationFixture;
use App\DataFixtures\OccurrenceFixture;
use App\DataFixtures\TagsFixtures;
use App\Entity\Address;
use App\Entity\Event;
use App\Entity\Feed;
use App\Entity\Location;
use App\Entity\Occurrence;
use App\Entity\Organization;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\Vocabulary;
use App\Tests\Fixtures\TestUserFixtures;
use App\Tests\Functional\AbstractAdminTestCase;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;

/**
 * Characterizes that every EasyAdmin DETAIL and EDIT page renders for a
 * super-admin against realistic (dev-fixture) data. Existing tests only cover
 * INDEX/NEW for most controllers, so a field-configurator or template
 * regression on DETAIL/EDIT would be invisible today. This is the broad safety
 * net for the EasyAdmin 5 upgrade, which reworks field rendering and templates.
 */
final class CrudRenderMatrixTest extends AbstractAdminTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures([
            AddressFixture::class,
            LocationFixture::class,
            TagsFixtures::class,
            FeedFixtures::class,
            EventFixture::class,
            OccurrenceFixture::class,
            DailyOccurrenceFixture::class,
            TestUserFixtures::class,
        ]);
    }

    private function firstId(string $entityClass): int|string
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $this->assertInstanceOf(EntityManagerInterface::class, $em);
        $entity = $em->getRepository($entityClass)->findOneBy([]);
        $this->assertNotNull($entity, sprintf('Expected at least one %s from fixtures', $entityClass));

        $id = $em->getUnitOfWork()->getSingleIdentifierValue($entity);
        $this->assertNotNull($id);

        return $id;
    }

    /**
     * @return iterable<string, array{class-string, class-string, list<string>}>
     */
    public static function crudProvider(): iterable
    {
        // [CRUD controller, entity class, actions to render]
        yield 'event' => [EventCrudController::class, Event::class, [Action::DETAIL, Action::EDIT]];
        yield 'organization' => [OrganizationCrudController::class, Organization::class, [Action::DETAIL, Action::EDIT]];
        yield 'location' => [LocationCrudController::class, Location::class, [Action::DETAIL, Action::EDIT]];
        yield 'address' => [AddressCrudController::class, Address::class, [Action::DETAIL, Action::EDIT]];
        yield 'tag' => [TagCrudController::class, Tag::class, [Action::DETAIL, Action::EDIT]];
        yield 'vocabulary' => [VocabularyCrudController::class, Vocabulary::class, [Action::DETAIL, Action::EDIT]];
        yield 'feed' => [FeedCrudController::class, Feed::class, [Action::DETAIL, Action::EDIT]];
        yield 'user' => [UserCrudController::class, User::class, [Action::DETAIL, Action::EDIT]];
        yield 'occurrence' => [EmbedOccurrenceCrudController::class, Occurrence::class, [Action::DETAIL, Action::EDIT]];
    }

    /**
     * @param class-string $crudControllerFqcn
     * @param class-string $entityClass
     * @param list<string> $actions
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('crudProvider')]
    public function testDetailAndEditRender(string $crudControllerFqcn, string $entityClass, array $actions): void
    {
        // Super-admin can reach every action (incl. super-admin-only Feed edit).
        $this->loginAs(TestUserFixtures::SUPER_ADMIN_EMAIL);
        $id = $this->firstId($entityClass);

        foreach ($actions as $action) {
            $this->client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, $this->adminUrl($crudControllerFqcn, $action, ['entityId' => $id]));
            $this->assertResponseIsSuccessful(sprintf('%s %s should render', $crudControllerFqcn, $action));
        }
    }
}
