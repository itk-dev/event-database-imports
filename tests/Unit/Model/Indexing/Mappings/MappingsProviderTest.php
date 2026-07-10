<?php

declare(strict_types=1);

namespace App\Tests\Unit\Model\Indexing\Mappings;

use App\Model\Indexing\IndexNames;
use App\Model\Indexing\Mappings\MappingsProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Guards the single source of truth for index mappings. Every index must
 * resolve to a non-empty property set (no unhandled match arm), and the
 * exported body must carry the `dynamic: strict` flag that createEsIndex()
 * applies — the committed resources/mappings/*.json depend on this shape.
 */
#[CoversClass(MappingsProvider::class)]
final class MappingsProviderTest extends TestCase
{
    #[DataProvider('indexProvider')]
    public function testEveryIndexResolvesToNonEmptyProperties(IndexNames $index): void
    {
        $this->assertNotEmpty(MappingsProvider::propertiesFor($index), $index->value.' must map to a non-empty property set');
    }

    #[DataProvider('indexProvider')]
    public function testMappingBodyIsStrictAndCarriesProperties(IndexNames $index): void
    {
        $mapping = MappingsProvider::mappingFor($index);

        $this->assertSame('strict', $mapping['dynamic'], $index->value.' mapping must be dynamic:strict');
        $this->assertSame(MappingsProvider::propertiesFor($index), $mapping['properties']);
    }

    public function testEventMappingCarriesNestedObjects(): void
    {
        $properties = MappingsProvider::propertiesFor(IndexNames::Events);

        // Composite event mapping embeds occurrences and the organizer/location objects.
        $this->assertArrayHasKey('occurrences', $properties);
        $this->assertArrayHasKey('dailyOccurrences', $properties);
        $this->assertArrayHasKey('properties', $properties['organizer']);
        $this->assertArrayHasKey('properties', $properties['location']);
    }

    public function testOccurrenceMappingCarriesParentEvent(): void
    {
        $properties = MappingsProvider::propertiesFor(IndexNames::Occurrences);

        $this->assertArrayHasKey('event', $properties);
        $this->assertArrayHasKey('properties', $properties['event']);
    }

    /**
     * @return iterable<string, array{IndexNames}>
     */
    public static function indexProvider(): iterable
    {
        foreach (IndexNames::cases() as $index) {
            yield $index->value => [$index];
        }
    }
}
