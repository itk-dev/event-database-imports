<?php

namespace App\Services\Feeds\Mapper\Source;

use Symfony\Component\PropertyAccess\PropertyAccess;

final class FeedNormalizedSource implements \IteratorAggregate
{
    private const SRC_WILDCARD = '*';
    private const SRC_SEPARATOR = '.';
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

            if(str_contains($src, self::SRC_SEPARATOR.self::SRC_WILDCARD.self::SRC_SEPARATOR)) {
                $values = $this->getValues([...$source], $src);
                $this->setValues($output, $dest, $values);

            }
            else {
                $value = $this->getValue([...$source], $src);
                $this->setValue($output, $dest, $value ?? '');
            }
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
        $key = $this->transformKey($src);

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
        $key = $this->transformKey($dest);

        $propertyAccessor->setValue($output, $key, $value);
    }

    /**
     * Get values from array indexed by wildcard src key string.
     *
     * @param array $data
     * @param string $src
     *
     * @return array
     */
    private function getValues(array $data, string $src): array
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
            ->disableExceptionOnInvalidPropertyPath()
            ->getPropertyAccessor();

        // Find nested array items by split on wildcard separator.
        $keys = explode(self::SRC_SEPARATOR.self::SRC_WILDCARD.self::SRC_SEPARATOR, $src);
        $key = $this->transformKey(reset($keys));
        $items = $propertyAccessor->getValue($data, $key);

        // Find nested array key and extra values.
        $key = $this->transformKey(array_pop($keys));
        $values = [];
        foreach ($items as $item) {
            $values[] = $propertyAccessor->getValue($item, $key);
        }

        return $values;
    }

    /**
     * @param array $output
     * @param string $dest
     * @param array $values
     * @return void
     */
    private function setValues(array &$output, string $dest, array $values): void
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
            ->disableExceptionOnInvalidPropertyPath()
            ->getPropertyAccessor();

        foreach ($values as $index => $value) {
            $key = str_replace(self::SRC_WILDCARD, $index, $dest);
            $key = $this->transformKey($key);
            $propertyAccessor->setValue($output, $key, $value);
        }
    }

    /**
     * Transform dot separated key into property accessor pattern.
     *
     * @param string $key
     *   The dot separated key.
     * @return string
     *   Key as property accessor pattern.
     */
    private function transformKey(string $key): string
    {
        return '['.implode('][', explode(self::SRC_SEPARATOR, $key)).']';
    }
}
