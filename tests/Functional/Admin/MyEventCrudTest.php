<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Controller\Admin\MyEventCrudController;
use App\DataFixtures\OrganizationFixtures;
use App\Tests\Fixtures\TestEventFixtures;
use App\Tests\Fixtures\TestUserFixtures;
use App\Tests\Functional\AbstractAdminTestCase;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;

final class MyEventCrudTest extends AbstractAdminTestCase
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

    public function testOrgEditorCanAccessIndex(): void
    {
        $this->loginAs(TestUserFixtures::ORG_EDITOR_A_EMAIL);
        $this->client->request('GET', $this->adminUrl(MyEventCrudController::class));

        $this->assertResponseIsSuccessful();
    }

    public function testOrgEditorCanAccessNew(): void
    {
        $this->loginAs(TestUserFixtures::ORG_EDITOR_A_EMAIL);
        $this->client->request('GET', $this->adminUrl(MyEventCrudController::class, Action::NEW));

        $this->assertResponseIsSuccessful();
    }
}
