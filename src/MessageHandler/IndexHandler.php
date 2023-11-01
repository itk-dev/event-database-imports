<?php

namespace App\MessageHandler;

use App\Exception\IndexingException;
use App\Message\IndexMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class IndexHandler
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly iterable $indexingServices,
        private readonly iterable $repositories,
    ) {
    }

    public function __invoke(IndexMessage $message): void
    {
        $indexingServices = $this->indexingServices instanceof \Traversable ? iterator_to_array($this->indexingServices) : $this->indexingServices;
        $repositories = $this->repositories instanceof \Traversable ? iterator_to_array($this->repositories) : $this->repositories;
        $index = $message->getIndexName()->value;

        $entity = $repositories[$index]->findOneBy(['id' => $message->getEntityId()]);
        if (!is_null($entity)) {
            try {
                $service = $indexingServices[$index];
                $service->index($entity);
            } catch (IndexingException $exception) {
                $this->logger->error(sprintf('Indexing exception: %s (%d) with entity id %d', $exception->getMessage(), $exception->getCode(), $message->getEntityId()));
            }
        } else {
            $this->logger->error(sprintf('Unable to index entity into index %s with id %d', $message->getIndexName()->value, $message->getEntityId()));
        }
    }
}
