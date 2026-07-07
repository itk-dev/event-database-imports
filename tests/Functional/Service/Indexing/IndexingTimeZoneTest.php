<?php

declare(strict_types=1);

namespace App\Tests\Functional\Service\Indexing;

use App\Controller\Admin\DashboardController;
use App\Entity\Organization;
use App\Service\Indexing\IndexingOrganizations;
use Elastic\Elasticsearch\Client;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Guards the model (UTC) -> view timezone boundary at the Elasticsearch index.
 *
 * Datetimes are stored in UTC; the index is written in the application view
 * timezone. These tests ensure that timezone is injected (not hardcoded) and
 * that the container wires it to the single application-wide source, so the
 * index and the admin display can never drift apart.
 */
final class IndexingTimeZoneTest extends KernelTestCase
{
    private function makeOrganization(): Organization
    {
        $org = new Organization();
        $org->setName('Timezone Test');
        // Stored in UTC (the model timezone).
        $org->setCreatedAt(new \DateTime('2026-07-01 12:00:00', new \DateTimeZone('UTC')));
        $org->setUpdatedAt(new \DateTime('2026-07-01 12:00:00', new \DateTimeZone('UTC')));

        return $org;
    }

    private function serializer(): SerializerInterface
    {
        $serializer = self::getContainer()->get(SerializerInterface::class);
        self::assertInstanceOf(SerializerInterface::class, $serializer);

        return $serializer;
    }

    private function client(): Client
    {
        // serialize() never touches the client; a real (unconnected) instance is fine.
        $client = self::getContainer()->get(Client::class);
        self::assertInstanceOf(Client::class, $client);

        return $client;
    }

    /**
     * The injected view timezone must drive the serialized datetime, proving it
     * is wired through rather than hardcoded to a single zone.
     */
    public function testInjectedViewTimezoneIsApplied(): void
    {
        $serializer = $this->serializer();
        $client = $this->client();

        $utc = new IndexingOrganizations($serializer, $client, 'UTC');
        $newYork = new IndexingOrganizations($serializer, $client, 'America/New_York');

        $utcUpdated = $utc->serialize($this->makeOrganization())['updated'];
        $nyUpdated = $newYork->serialize($this->makeOrganization())['updated'];

        // 12:00 UTC stays 12:00+00:00; in New York (EDT in July) it is 08:00-04:00.
        self::assertStringContainsString('2026-07-01T12:00:00', $utcUpdated);
        self::assertStringEndsWith('+00:00', $utcUpdated);
        self::assertStringContainsString('2026-07-01T08:00:00', $nyUpdated);
        self::assertStringEndsWith('-04:00', $nyUpdated);
    }

    /**
     * The container-wired service must use the application view timezone, so the
     * index matches what the admin renders (single source of truth).
     */
    public function testContainerServiceUsesApplicationViewTimezone(): void
    {
        $indexing = self::getContainer()->get(IndexingOrganizations::class);
        self::assertInstanceOf(IndexingOrganizations::class, $indexing);

        $updated = $indexing->serialize($this->makeOrganization())['updated'];

        // Europe/Copenhagen in July is CEST (+02:00): 12:00 UTC -> 14:00+02:00.
        self::assertSame('Europe/Copenhagen', DashboardController::VIEW_TIMEZONE);
        self::assertStringContainsString('2026-07-01T14:00:00', $updated);
        self::assertStringEndsWith('+02:00', $updated);
    }
}
