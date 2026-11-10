<?php

declare(strict_types=1);

namespace App\Tests\Functional\Command;

use App\Command\Messenger\PurgeFailedCommand;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Messenger stores created_at in UTC, while MariaDB runs in Europe/Copenhagen on the servers (ADR 008).
 * The purge must compare against UTC, not the session time zone.
 */
#[CoversClass(PurgeFailedCommand::class)]
final class PurgeFailedCommandTest extends KernelTestCase
{
    public function testPurgesByUtcRegardlessOfSessionTimeZone(): void
    {
        $kernel = self::bootKernel();
        $connection = self::getContainer()->get(Connection::class);
        $this->assertInstanceOf(Connection::class, $connection);

        // Simulate the servers: session time zone one hour ahead of UTC.
        $connection->executeStatement("SET time_zone = '+01:00'");
        $connection->executeStatement('DELETE FROM messenger_messages');

        $now = \Carbon\Carbon::now()->getTimestamp();
        $insert = static fn (string $body, int $createdAt): int|string => $connection->insert('messenger_messages', [
            'body' => $body,
            'headers' => '[]',
            'queue_name' => 'failed',
            'created_at' => gmdate('Y-m-d H:i:s', $createdAt),
            'available_at' => gmdate('Y-m-d H:i:s', $createdAt),
        ]);
        $insert('keep', $now - 15 * 86400 + 1800);  // 15 days minus 30 minutes old
        $insert('purge', $now - 15 * 86400 - 1800); // 15 days plus 30 minutes old

        $tester = new CommandTester(new Application($kernel)->find('app:messenger:purge-failed'));
        $tester->execute(['--since' => '15']);

        $tester->assertCommandIsSuccessful();
        $this->assertSame(['keep'], $connection->fetchFirstColumn('SELECT body FROM messenger_messages'));
    }
}
