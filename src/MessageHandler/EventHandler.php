<?php

namespace App\MessageHandler;

use App\Factory\EventFactory;
use App\Message\EventMessage;
use App\Message\ImageMessage;
use App\Repository\FeedItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final readonly class EventHandler
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private EventFactory $eventFactory,
        private FeedItemRepository $feedItemRepository,
        private EntityManagerInterface $entityManager,
        private ManagerRegistry $managerRegistry,
    ) {
    }

    public function __invoke(EventMessage $message): void
    {
        $feedItemData = $message->getFeedItemData();

        $feedItemEntity = $this->feedItemRepository->findOneBy(['feed' => $feedItemData->feedId, 'feedItemId' => $feedItemData->id]);
        if (null === $feedItemEntity) {
            throw new UnrecoverableMessageHandlingException('No feed item entity found for feed ID '.$feedItemData->feedId);
        }

        try {
            $event = $this->eventFactory->createOrUpdate($feedItemData, $feedItemEntity);

            $feedItemEntity->setEvent($event);
            $this->entityManager->persist($feedItemEntity);
            $this->entityManager->flush();

            $id = $event->getId();
            if (!is_null($id)) {
                $this->messageBus->dispatch(new ImageMessage($id, $event->getImage()?->getId()));
            } else {
                throw new UnrecoverableMessageHandlingException('Event without id detected');
            }
        } catch (\Exception $e) {
            // EntityManager is closed if this is a doctrine exception
            $this->managerRegistry->resetManager();

            $feedItemEntity = $this->feedItemRepository->find($feedItemEntity->getId());
            $feedItemEntity?->setMessage($e->getMessage());

            $this->entityManager->flush();

            throw new UnrecoverableMessageHandlingException($e->getMessage());
        }
    }
}
