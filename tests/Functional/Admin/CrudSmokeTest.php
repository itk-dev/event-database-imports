<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Controller\Admin\AddressCrudController;
use App\Controller\Admin\EmbedImageController;
use App\Controller\Admin\EmbedOccurrenceCrudController;
use App\Controller\Admin\FeedCrudController;
use App\Controller\Admin\FeedItemCrudController;
use App\Controller\Admin\LocationCrudController;
use App\Controller\Admin\MyEventCrudController;
use App\Controller\Admin\MyOrganizationCrudController;
use App\Controller\Admin\TagCrudController;
use App\Controller\Admin\VocabularyCrudController;
use App\DataFixtures\OrganizationFixtures;
use App\Tests\Fixtures\TestUserFixtures;
use App\Tests\Functional\AbstractAdminTestCase;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Role-gating smoke coverage for CRUD controllers whose only app-specific
 * behaviour at the URL level is "which roles are allowed to see this page".
 * Consolidates what were previously nine thin per-entity test classes into
 * one parameterized matrix: if you add a CRUD controller with non-trivial
 * app behaviour (cross-org filtering, entity-level voters beyond role
 * checks, custom actions), give it its own test file alongside EventCrudTest
 * and OrganizationCrudTest instead of adding a row here.
 */
final class CrudSmokeTest extends AbstractAdminTestCase
{
    private const EXPECT_SUCCESS = 'success';
    private const EXPECT_DENIED = 'denied';

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures([
            OrganizationFixtures::class,
            TestUserFixtures::class,
        ]);
    }

    /**
     * @param 'success'|'denied' $expected
     */
    #[DataProvider('provideRoleMatrix')]
    public function testRoleMatrix(string $controller, string $action, string $email, string $expected): void
    {
        $this->loginAs($email);
        $this->client->request('GET', $this->adminUrl($controller, $action));

        if (self::EXPECT_SUCCESS === $expected) {
            $this->assertResponseIsSuccessful();

            return;
        }

        $status = $this->client->getResponse()->getStatusCode();
        $this->assertContains(
            $status,
            [302, 403],
            sprintf('Expected access denied (302 or 403), got %d', $status),
        );
    }

    /**
     * @return iterable<string, array{string, string, string, 'success'|'denied'}>
     */
    public static function provideRoleMatrix(): iterable
    {
        // Editors can browse address / location / tag index + new pages.
        yield 'editor: address index' => [AddressCrudController::class, Action::INDEX, TestUserFixtures::EDITOR_EMAIL, self::EXPECT_SUCCESS];
        yield 'editor: address new' => [AddressCrudController::class, Action::NEW, TestUserFixtures::EDITOR_EMAIL, self::EXPECT_SUCCESS];
        yield 'editor: location index' => [LocationCrudController::class, Action::INDEX, TestUserFixtures::EDITOR_EMAIL, self::EXPECT_SUCCESS];
        yield 'editor: location new' => [LocationCrudController::class, Action::NEW, TestUserFixtures::EDITOR_EMAIL, self::EXPECT_SUCCESS];
        yield 'editor: tag index' => [TagCrudController::class, Action::INDEX, TestUserFixtures::EDITOR_EMAIL, self::EXPECT_SUCCESS];
        yield 'org editor: tag index' => [TagCrudController::class, Action::INDEX, TestUserFixtures::ORG_EDITOR_A_EMAIL, self::EXPECT_SUCCESS];

        // Org editors get their own "My*" screens.
        yield 'org editor: my event index' => [MyEventCrudController::class, Action::INDEX, TestUserFixtures::ORG_EDITOR_A_EMAIL, self::EXPECT_SUCCESS];
        yield 'org editor: my event new' => [MyEventCrudController::class, Action::NEW, TestUserFixtures::ORG_EDITOR_A_EMAIL, self::EXPECT_SUCCESS];
        yield 'org editor: my organization index' => [MyOrganizationCrudController::class, Action::INDEX, TestUserFixtures::ORG_EDITOR_A_EMAIL, self::EXPECT_SUCCESS];

        // Embed admin screens available to editors.
        yield 'editor: embed image index' => [EmbedImageController::class, Action::INDEX, TestUserFixtures::EDITOR_EMAIL, self::EXPECT_SUCCESS];
        yield 'editor: embed occurrence index' => [EmbedOccurrenceCrudController::class, Action::INDEX, TestUserFixtures::EDITOR_EMAIL, self::EXPECT_SUCCESS];

        // Feed / feed-item / vocabulary are admin-only.
        yield 'admin: feed index' => [FeedCrudController::class, Action::INDEX, TestUserFixtures::ADMIN_EMAIL, self::EXPECT_SUCCESS];
        yield 'editor denied: feed index' => [FeedCrudController::class, Action::INDEX, TestUserFixtures::EDITOR_EMAIL, self::EXPECT_DENIED];
        yield 'admin: feed item index' => [FeedItemCrudController::class, Action::INDEX, TestUserFixtures::ADMIN_EMAIL, self::EXPECT_SUCCESS];
        yield 'editor denied: feed item index' => [FeedItemCrudController::class, Action::INDEX, TestUserFixtures::EDITOR_EMAIL, self::EXPECT_DENIED];
        yield 'admin: vocabulary index' => [VocabularyCrudController::class, Action::INDEX, TestUserFixtures::ADMIN_EMAIL, self::EXPECT_SUCCESS];
        yield 'editor denied: vocabulary index' => [VocabularyCrudController::class, Action::INDEX, TestUserFixtures::EDITOR_EMAIL, self::EXPECT_DENIED];
    }
}
