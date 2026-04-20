<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Repository\UserRepository;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractAdminTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected AbstractDatabaseTool $databaseTool;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->databaseTool = static::getContainer()
            ->get(DatabaseToolCollection::class)
            ->get();
    }

    /**
     * @param list<class-string> $classes
     */
    protected function loadFixtures(array $classes): void
    {
        $this->databaseTool->loadFixtures($classes);
    }

    protected function loginAs(string $email): KernelBrowser
    {
        $repository = static::getContainer()->get(UserRepository::class);
        $user = $repository->findOneBy(['mail' => $email]);

        if (null === $user) {
            throw new \RuntimeException(sprintf('User "%s" not found. Did you load TestUserFixtures?', $email));
        }

        $this->client->loginUser($user);

        return $this->client;
    }
}
