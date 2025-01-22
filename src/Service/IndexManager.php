<?php

namespace App\Service;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Response\Elasticsearch;

readonly class IndexManager
{
    public function __construct(private Client $client)
    {
    }

    public function getAll(): array
    {
        // Fetch all indices
        /** @var Elasticsearch $response */
        $response = $this->client->cat()->indices(['format' => 'JSON', 'expand_wildcards' => 'open']);
        $indices = $response->asArray();

        // Fetch all aliases
        /** @var Elasticsearch $response */
        $response = $this->client->cat()->aliases(['format' => 'JSON', 'expand_wildcards' => 'all']);
        $aliasesData = $response->asArray();

        // Map aliases to their indices
        $aliasesMap = [];
        foreach ($aliasesData as $alias) {
            $indexName = $alias['index'] ?? 'unknown';
            $aliasName = $alias['alias'] ?? 'unknown';
            $aliasesMap[$indexName][] = $aliasName;
        }

        // Prepare table data
        $indicesData = [];
        foreach ($indices as $index) {
            $indexName = $index['index'] ?? 'unknown';
            $index['aliases'] = !empty($aliasesMap[$indexName]) ? $aliasesMap[$indexName] : [];
            $indicesData[] = $index;
        }

        return $indicesData;
    }

    public function deleteNonAliased(): int
    {
        $indices = $this->getAll();

        $deletedCount = 0;
        foreach ($indices as $index) {
            if (empty($index['aliases'])) {
                $this->client->indices()->delete(['index' => $index['index']]);
                ++$deletedCount;
            }
        }

        return $deletedCount;
    }
}
