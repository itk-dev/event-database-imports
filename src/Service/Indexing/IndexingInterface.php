<?php

namespace App\Service\Indexing;

use App\Exception\IndexingException;
use App\Model\Indexing\IndexItemInterface;

interface IndexingInterface
{
    /**
     * Add single item to the index.
     *
     * @param IndexItemInterface $item
     *   Item to add to the index
     *
     * @throws IndexingException
     */
    public function index(IndexItemInterface $item): void;

    /**
     * Remove single item from the index.
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
}