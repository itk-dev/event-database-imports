<?php

namespace App\Model\Indexing\Mappings;

class OccurrenceWithEvent implements MappingsInterface
{
    #[\Override]
    public static function getProperties(): array
    {
        $properties = Occurrence::getProperties();

        $properties['event'] = ['properties' => Event::PROPERTIES];

        return $properties;
    }
}
