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
     * @param string $path
     *   The file to store the json file in
     *
     * @return \Generator
     *   Yield back progress
     */
    public function dump(string $index, string $path): \Generator
    {
        $indexingServices = $this->indexingServices instanceof \Traversable ? iterator_to_array($this->indexingServices) : $this->indexingServices;

        $path = rtrim($path, '/').'/';
        $file = $path.$index.'.json';

        $filesystem = new Filesystem();
        $filesystem->dumpFile($file, '[');
        foreach ($indexingServices[$index]->dumpIndex() as $i => $item) {
            yield sprintf('%s: Fetched document "%s"', ucfirst($index), $item['name'] ?? $item['title'] ?? 'unknown');
            $filesystem->appendToFile($file, (0 !== $i ? ',' : '').json_encode($item, JSON_PRETTY_PRINT));
        }

        $filesystem->appendToFile($file, ']');
    }
}
