<?php

namespace App\Services\Mapper\Source;

use Symfony\Component\PropertyAccess\PropertyAccess;

final class FeedNormalizedSource implements \IteratorAggregate
{
    private iterable $source;

    public function __construct(iterable $source, array $mappings)
    {
        $this->source = $this->doSomething($source, $mappings);
    }

    private function doSomething(iterable $source, array $mappings): iterable
    {
        $output = [];

        foreach ($mappings as $src => $dest) {
            $data = $this->getValue([...$source], $src);
            $output[$dest] = $data ?? '';
        }

        return $output;
    }

    public function getIterator(): \Traversable
    {
        yield from $this->source;
    }

    private function getValue(array $data, string $src)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
            ->disableExceptionOnInvalidPropertyPath()
            ->getPropertyAccessor();
        $key = explode('.', $src);
        $key = '['.implode('][', $key).']';

        return $propertyAccessor->getValue($data, $key);
    }
}
