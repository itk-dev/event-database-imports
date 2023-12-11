<?php
/**
 * @file
 * Contains a service to populate search index.
 */

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Class Dump.
 */
final class Dump
{
    public function __construct(
        private readonly iterable $indexingServices,
    ) {
    }

    /**
     * Dump data from search index as JSON.
     *
     * @param string $index
     *   The index to dump into a file
     * @param string $file
     *   The file to store the json int
     *
     * @return \Generator
     *   Yield back progress
     */
    public function dump(string $index, string $file): \Generator
    {
        $indexingServices = $this->indexingServices instanceof \Traversable ? iterator_to_array($this->indexingServices) : $this->indexingServices;

        $filesystem = new Filesystem();
        $filesystem->dumpFile($file, '[');
        foreach ($indexingServices[$index]->dumpIndex() as $i => $item) {
            yield sprintf('Fetched document "%s"', $item['name'] ?? $item['title'] ?? 'unknown');
            $filesystem->appendToFile($file, (0 !== $i ? ',' : '').json_encode($item, JSON_PRETTY_PRINT));
        }

        $filesystem->appendToFile($file, ']');
    }
}
