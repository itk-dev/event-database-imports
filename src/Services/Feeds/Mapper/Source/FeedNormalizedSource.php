<?php

namespace App\Services\Feeds\Mapper\Source;

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
            $value = $this->getValue([...$source], $src);
            $this->setValue($output, $dest, $value ?? '');
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

    /**
     * Set value in output based on dot separated array indexes.
     *
     * @param array $output
     *   The array to insert data into.
     * @param string $dest
     *   The array location to insert data into (levels operated by a '.' dot).
     * @param $value
     *   The value to insert into the array location.
     *
     * @return void
     */
    private function setValue(array &$output, string $dest, $value): void
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
            ->disableExceptionOnInvalidPropertyPath()
            ->getPropertyAccessor();
        $key = explode('.', $dest);
        $key = '['.implode('][', $key).']';

        $propertyAccessor->setValue($output, $key, $value);
    }
}
