<?php

namespace App\Service\Feeds\Mapper\Source;

use App\Model\Feed\FeedConfiguration;
use App\Service\Feeds\FeedDefaultsMapper;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Source normalizer that is executed before mapping feed data to object.
 *
 * It handles the "." dot notation in mappings to enabled mapping of dimensional data (array indexes) to object values.
 * It handles the ".*." wildcard notation to map arrays into arrays with different keys.
 *
 * @see https://valinor.cuyz.io/1.5/how-to/transform-input/#custom-source
 */
final class FeedItemSource
{
    private const string SRC_WILDCARD = '*';
    private const string SRC_SEPARATOR = '.';

    public function __construct(
        private readonly FeedConfiguration $configuration,
        private readonly FeedDefaultsMapper $defaultsMapperService
    ) {
    }

    /**
     * Normalize array data into mappings format.
     *
     * @param iterable $source
     *   The input source as iterable array
     *
     * @return iterable
     *   Normalized array
     */
    public function normalize(iterable $source): iterable
    {
        $output = [];

        foreach ($this->configuration->mapping as $src => $dest) {
            // Match dest that ends with ".[OPERATOR]". to map to array
            if (preg_match('/(?P<dest>.*)\.\[(?P<separator>.*)]/', $dest, $matches)) {
                $separator = $matches['separator'];
                $value = $this->getValue([...$source], $src);
                $values = empty($separator) ? [$value] : explode($separator, $value);
                $this->setValue($output, $matches['dest'], $values);
            }
            // Match src with ".*." multi value array mapping.
            elseif (str_contains($src, self::SRC_SEPARATOR.self::SRC_WILDCARD.self::SRC_SEPARATOR)) {
                $values = $this->getValues([...$source], $src);
                $this->setValues($output, $dest, $values);
            }
            // Match dest with ".*." single value into array mapping.
            elseif (str_contains($dest, self::SRC_SEPARATOR.self::SRC_WILDCARD.self::SRC_SEPARATOR)) {
                $value = $this->getValue([...$source], $src);
                $exploded = explode(self::SRC_SEPARATOR.self::SRC_WILDCARD.self::SRC_SEPARATOR, $dest);
                $key = array_shift($exploded);
                $key = $this->transformKey($key);

                $propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
                    ->disableExceptionOnInvalidPropertyPath()
                    ->getPropertyAccessor();

                $valueCount = count($propertyAccessor->getValue($output, $key));
                $values = array_fill(0, $valueCount, $value);

                $this->setValues($output, $dest, $values);
            } else {
                $value = $this->getValue([...$source], $src);
                $this->setValue($output, $dest, $value);
            }
        }

        // Apply default values.
        return $this->defaultsMapperService->apply($output, $this->configuration);
    }

    /**
     * Get value from data based on dot separated array indexes.
     *
     * @param array $data
     *   Input data
     * @param string $src
     *   Index into input data as string
     *
     * @return mixed
     *   The content of the index found or null if not found
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
     *   The array to insert data into
     * @param string $dest
     *   The array location to insert data into (levels operated by a '.' dot).
     * @param mixed $value
     *   The value to insert into the array location
     *
     * @psalm-param list{mixed|string,...} $value
     */
    private function setValue(array &$output, string $dest, mixed $value): void
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
     *   The raw input data
     * @param string $src
     *   The keys to look up the values under
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
        $items = $items ?? [];

        // Find nested array key and extra values.
        $key = $this->transformKey(array_pop($keys));
        $values = [];
        foreach ($items as $item) {
            $values[] = $propertyAccessor->getValue($item, $key);
        }

        return $values;
    }

    /**
     * Set values from array to array in output.
     *
     * @param array $output
     *   The result
     * @param string $dest
     *   The destination key(s)
     * @param array $values
     *   The values to insert based on the key
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
     *   The dot separated key
     *
     * @return string
     *   Key as property accessor pattern
     */
    private function transformKey(string $key): string
    {
        return '['.implode('][', explode(self::SRC_SEPARATOR, $key)).']';
    }
}
