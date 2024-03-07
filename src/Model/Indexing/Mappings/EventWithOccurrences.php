<?php

namespace App\Model\Indexing\Mappings;

class EventWithOccurrences implements MappingsInterface
{
    #[\Override]
    public static function getProperties(): array
    {
        $properties = Event::getProperties();

        $properties['occurrences'] = ['properties' => Occurrence::PROPERTIES];
        $properties['dailyOccurrences'] = ['properties' => Occurrence::PROPERTIES];

        return $properties;
    }
}
