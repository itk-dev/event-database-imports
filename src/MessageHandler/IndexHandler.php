<?php

namespace App\MessageHandler;

use App\Exception\IndexingException;
use App\Message\IndexMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler]
final readonly class IndexHandler
{
    public function __construct(
        private LoggerInterface $logger,
        private iterable $indexingServices,
        private iterable $repositories,
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
            } catch (IndexingException $e) {
                $this->logger->error(sprintf('Indexing exception: %s (%d) with entity id %d', $e->getMessage(), $e->getCode(), $message->getEntityId()));
            }
        } else {
            $this->logger->error(sprintf('Unable to index entity into index %s with id %d', $message->getIndexName()->value, $message->getEntityId()));

            throw new UnrecoverableMessageHandlingException('Unable to index entity into index %s with id %d', $message->getIndexName()->value, $message->getEntityId());
        }
    }
}
