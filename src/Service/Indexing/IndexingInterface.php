<?php

namespace App\Service\Indexing;

use App\Exception\IndexingException;

interface IndexingInterface
{
    /**
     * Add a single item to the index.
     *
     * @param IndexItemInterface $item
     *   Item to add to the index
     *
     * @throws IndexingException
     */
    public function index(IndexItemInterface $item): void;

    /**
     * Remove a single item from the index.
     *
     * @param int $id
     *   ID of the item to remove
     *
     * @throws IndexingException
     */
    public function delete(int $id): void;

    /**
     * Bulk add IndexItem objects.
     *
     * @param IndexItemInterface[] $items
     *   Array of IndexItem to add to the index
     *
     * @throws IndexingException
     */
    public function bulk(array $items): void;

    /**
     * Switch new index with old.
     *
     * @throws IndexingException
     */
    public function switchIndex(): void;

    /**
     * Create index.
     *
     * @throws IndexingException
     */
    public function createIndex(): void;

    /**
     * Verify that index exists.
     *
     * @throws IndexingException
     */
    public function indexExists(): bool;

    /**
     * Healer to convert entities to serialized data that the indexer understands.
     *
     * @param IndexItemInterface $item
     *    Item to add to the index
     *
     * @return array
     *   The serialized data
     *
     * @throws IndexingException
     */
    public function serialize(IndexItemInterface $item): array;

    /**
     * Get doctrine criteria to limit indexing.
     *
     * If only a subset of the entities need to be indexed
     * a criteria can be defined to limit the selection.
     */
    public function criteria(): array;

    public function dumpIndex(): \Generator;
}
