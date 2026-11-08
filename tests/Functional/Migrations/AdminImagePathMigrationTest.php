<?php

declare(strict_types=1);

namespace App\Tests\Functional\Migrations;

use App\Entity\Image;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManagerInterface;
use DoctrineMigrations\Version20261108165127;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * The migration rewrites admin image paths in prod data, so check it against every stored format, not only that it
 * runs on an empty database.
 */
#[CoversClass(Version20261108165127::class)]
final class AdminImagePathMigrationTest extends KernelTestCase
{
    public function testUpRewritesAdminUploadsOnly(): void
    {
        $result = $this->migrate([
            'legacy admin upload' => '/uploads/a.png',
            'plain admin upload' => 'a2.png',
            'new admin upload' => 'uploads/b.png',
            'fetched feed image' => '2f75fc1b/c.jpg',
            'upload folder (broken)' => '/uploads',
            'empty' => '',
            'no file' => null,
        ], 'up');

        $this->assertSame([
            'legacy admin upload' => 'uploads/a.png',
            'plain admin upload' => 'uploads/a2.png',
            'new admin upload' => 'uploads/b.png',
            'fetched feed image' => '2f75fc1b/c.jpg',
            'upload folder (broken)' => '/uploads',
            'empty' => '',
            'no file' => null,
        ], $result);
    }

    /**
     * down() restores "/uploads/<file>". It cannot tell which uploads were stored as the plain file name before up(),
     * so those also get "/uploads/<file>"; that is harmless (it is what the old image import wrote).
     */
    public function testDownRestoresLegacyUploadPath(): void
    {
        $result = $this->migrate([
            'admin upload' => 'uploads/a.png',
            'fetched feed image' => '2f75fc1b/c.jpg',
            'no file' => null,
        ], 'down');

        $this->assertSame([
            'admin upload' => '/uploads/a.png',
            'fetched feed image' => '2f75fc1b/c.jpg',
            'no file' => null,
        ], $result);
    }

    /**
     * @param array<string, ?string> $locals stored local values keyed by a label
     *
     * @return array<string, ?string> the local values after the migration, keyed by the same label
     */
    private function migrate(array $locals, string $direction): array
    {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);

        $ids = [];
        foreach ($locals as $label => $local) {
            $image = new Image()->setTitle($label)->setSource('https://example.com/'.$label)->setLocal($local);
            $entityManager->persist($image);
            $entityManager->flush();
            $ids[$label] = $image->getId();
        }

        // Migrations are loaded by Doctrine Migrations, not by the autoloader.
        require_once self::getContainer()->getParameter('kernel.project_dir').'/migrations/Version20261108165127.php';
        $migration = new Version20261108165127($entityManager->getConnection(), new NullLogger());
        'up' === $direction ? $migration->up(new Schema()) : $migration->down(new Schema());
        foreach ($migration->getSql() as $query) {
            $entityManager->getConnection()->executeStatement($query->getStatement(), $query->getParameters(), $query->getTypes());
        }

        $result = [];
        foreach ($ids as $label => $id) {
            $result[$label] = $entityManager->getConnection()->fetchOne('SELECT local FROM image WHERE id = ?', [$id]);
        }

        return $result;
    }
}
