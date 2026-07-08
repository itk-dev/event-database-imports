<?php

namespace App\Model\Indexing\Mappings;

use App\Model\Indexing\IndexNames;

/**
 * Single source of truth for the Elasticsearch mapping of each index.
 *
 * Used both when creating an index (AbstractIndexingElastic::createEsIndex())
 * and when exporting the committed schema (IndexMappingsDumpCommand), so the
 * two can never drift apart.
 */
final class MappingsProvider
{
    /**
     * The mapping properties (field definitions) for an index.
     *
     * @return array<string, mixed>
     */
    public static function propertiesFor(IndexNames $index): array
    {
        return match ($index) {
            IndexNames::Organizations => Organizer::getProperties(),
            IndexNames::Events => EventWithOccurrences::getProperties(),
            IndexNames::Locations => Location::getProperties(),
            IndexNames::Tags => Tag::getProperties(),
            IndexNames::Vocabularies => Vocabularies::getProperties(),
            IndexNames::Occurrences, IndexNames::DailyOccurrences => OccurrenceWithEvent::getProperties(),
        };
    }

    /**
     * The full mapping body applied at index creation — schema only, no settings.
     *
     * @return array{dynamic: string, properties: array<string, mixed>}
     */
    public static function mappingFor(IndexNames $index): array
    {
        return [
            'dynamic' => 'strict',
            'properties' => self::propertiesFor($index),
        ];
    }
}
