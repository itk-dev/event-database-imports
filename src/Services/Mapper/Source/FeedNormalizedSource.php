<?php

namespace App\Services\Mapper\Source;

use Symfony\Component\PropertyAccess\PropertyAccess;

final class FeedNormalizedSource implements \IteratorAggregate
{
    private iterable $source;

    public function __construct(iterable $source, array $mappings)
    {
        $this->source = $this->normalize($source, $mappings);
    }

    /**
     * Normalize array data into mappings format.
     *
     * @param iterable $source
     *   The input source as iterable array.
     * @param array $mappings
     *   Mappings defined.
     *
     * @return iterable
     *   Normalized array.
     */
    private function normalize(iterable $source, array $mappings): iterable
    {
        $output = [];

        foreach ($mappings as $src => $dest) {
            $data = $this->getValue([...$source], $src);
            $output[$dest] = $data ?? '';
        }

        return $output;
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): \Traversable
    {
        yield from $this->source;
    }

    /**
     * Get value from data based on dot separated array indexes.
     *
     * @param array $data
     *   Input data.
     * @param string $src
     *   Index into input data as string.
     *
     * @return mixed
     *   The content of the index found or null if not found.
     */
    private function getValue(array $data, string $src): mixed
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
            ->disableExceptionOnInvalidPropertyPath()
            ->getPropertyAccessor();
        $key = explode('.', $src);
        $key = '['.implode('][', $key).']';

        return $propertyAccessor->getValue($data, $key);
    }
}
