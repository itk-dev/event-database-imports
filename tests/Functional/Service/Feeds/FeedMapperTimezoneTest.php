<?php

declare(strict_types=1);

namespace App\Tests\Functional\Service\Feeds;

use App\Model\Feed\FeedConfiguration;
use App\Service\Feeds\Mapper\FeedMapperInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * A feed occurrence datetime without an explicit offset must be interpreted in
 * the feed's declared timezone — not the worker's ambient PHP timezone (UTC in
 * production, Europe/Copenhagen in dev). All feeds declare Europe/Copenhagen;
 * the naive-format ones (e.g. Radar, Aros) are currently parsed in the ambient
 * timezone, so in production they are stored/served 1–2h off.
 *
 * Each test forces the ambient timezone to UTC to reproduce the production
 * worker and make the assertions independent of the environment's own default.
 */
final class FeedMapperTimezoneTest extends KernelTestCase
{
    private string $originalTimezone;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalTimezone = date_default_timezone_get();
        // Reproduce the production async worker (supervisor: PHP_TIMEZONE=UTC).
        date_default_timezone_set('UTC');
    }

    protected function tearDown(): void
    {
        date_default_timezone_set($this->originalTimezone);
        parent::tearDown();
    }

    private function mapper(): FeedMapperInterface
    {
        $mapper = self::getContainer()->get(FeedMapperInterface::class);
        $this->assertInstanceOf(FeedMapperInterface::class, $mapper);

        return $mapper;
    }

    private function config(string $dateFormat): FeedConfiguration
    {
        return new FeedConfiguration(
            type: 'json',
            url: 'https://example.test/feed',
            base: 'https://example.test/',
            timezone: 'Europe/Copenhagen',
            rootPointer: '/-',
            dateFormat: $dateFormat,
            mapping: [
                'id' => 'id',
                'occurrences.*.start' => 'occurrences.*.start',
                'occurrences.*.end' => 'occurrences.*.end',
            ],
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function firstStartAsUtc(array $data, string $dateFormat): string
    {
        $item = $this->mapper()->getFeedItemFromArray($data, $this->config($dateFormat));
        $start = $item->occurrences[0]->start;
        $this->assertInstanceOf(\DateTimeImmutable::class, $start);

        return $start->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i');
    }

    /**
     * Offset-less datetime (Radar-style `Y-m-d\TH:i`) → interpreted as
     * Europe/Copenhagen. 20:45 CEST (August) == 18:45 UTC.
     */
    public function testNaiveDatetimeIsInterpretedInFeedTimezone(): void
    {
        $data = ['id' => 'evt-1', 'occurrences' => [['start' => '2026-08-15T20:45', 'end' => '2026-08-15T21:45']]];

        $this->assertSame('2026-08-15 18:45', $this->firstStartAsUtc($data, 'Y-m-d\TH:i'));
    }

    /**
     * Offset-less datetime in winter (Aros-style `Y-m-d\TH:i:s`) → Europe/Copenhagen.
     * 11:00 CET (March) == 10:00 UTC.
     */
    public function testNaiveWinterDatetimeIsInterpretedInFeedTimezone(): void
    {
        $data = ['id' => 'evt-2', 'occurrences' => [['start' => '2026-03-03T11:00:00', 'end' => '2026-03-03T12:00:00']]];

        $this->assertSame('2026-03-03 10:00', $this->firstStartAsUtc($data, 'Y-m-d\TH:i:s'));
    }

    /**
     * Offset-bearing datetime (Bora Bora-style `Y-m-d\TH:i:sP`) keeps its own
     * offset regardless of the feed timezone or the ambient timezone.
     * 19:30+02:00 == 17:30 UTC.
     */
    public function testOffsetBearingDatetimeKeepsItsOffset(): void
    {
        $data = ['id' => 'evt-3', 'occurrences' => [['start' => '2026-09-04T19:30:00+02:00', 'end' => '2026-09-04T21:00:00+02:00']]];

        $this->assertSame('2026-09-04 17:30', $this->firstStartAsUtc($data, 'Y-m-d\TH:i:sP'));
    }
}
