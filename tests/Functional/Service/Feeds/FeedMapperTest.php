<?php

declare(strict_types=1);

namespace App\Tests\Functional\Service\Feeds;

use App\Model\Feed\FeedConfiguration;
use App\Service\Feeds\Mapper\FeedMapperInterface;
use CuyZ\Valinor\Mapper\MappingError;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Guards the Valinor feed-item mapping behaviours that change across the
 * 1.x → 2.x upgrade: scalar value casting and the MappingError branch. The
 * timezone/date-format behaviour is covered by FeedMapperTimezoneTest.
 */
final class FeedMapperTest extends KernelTestCase
{
    private function mapper(): FeedMapperInterface
    {
        $mapper = self::getContainer()->get(FeedMapperInterface::class);
        $this->assertInstanceOf(FeedMapperInterface::class, $mapper);

        return $mapper;
    }

    private function config(): FeedConfiguration
    {
        return new FeedConfiguration(
            type: 'json',
            url: 'https://example.test/feed',
            base: 'https://example.test/',
            timezone: 'Europe/Copenhagen',
            rootPointer: '/-',
            dateFormat: 'Y-m-d\TH:i:sP',
            mapping: [
                'id' => 'id',
                'occurrences.*.start' => 'occurrences.*.start',
                'occurrences.*.end' => 'occurrences.*.end',
                'occurrences.*.price' => 'occurrences.*.price',
            ],
        );
    }

    /**
     * Scalar value casting: a numeric price in the source is cast to the target
     * string type. Guards the enableFlexibleCasting() -> allowScalarValueCasting()
     * migration in Valinor 2.x.
     */
    public function testNumericPriceIsCastToString(): void
    {
        $data = [
            'id' => 'evt-1',
            'occurrences' => [[
                'start' => '2026-08-15T20:45:00+02:00',
                'end' => '2026-08-15T21:45:00+02:00',
                'price' => 100,
            ]],
        ];

        $item = $this->mapper()->getFeedItemFromArray($data, $this->config());

        $this->assertSame('evt-1', $item->id);
        $this->assertSame('100', $item->occurrences[0]->price);
    }

    /**
     * The MappingError branch: a missing required id throws. Guards the
     * Messages::flattenFromNode() -> MappingError::messages() migration.
     */
    public function testMissingRequiredIdThrowsMappingError(): void
    {
        $data = [
            'occurrences' => [[
                'start' => '2026-08-15T20:45:00+02:00',
                'end' => '2026-08-15T21:45:00+02:00',
            ]],
        ];

        $this->expectException(MappingError::class);

        $this->mapper()->getFeedItemFromArray($data, $this->config());
    }
}
