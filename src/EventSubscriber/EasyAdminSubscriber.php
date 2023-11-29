<?php

namespace App\EventSubscriber;

use App\Entity\Address;
use App\Entity\Event;
use App\Entity\Image;
use App\Entity\Location;
use App\Entity\Tag;
use App\Message\ImageMessage;
use App\Service\EventFinder;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly LoggerInterface $logger,
        private readonly EventFinder $eventFinder,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityUpdatedEvent::class => ['beforeEntityUpdatedEvent'],
            BeforeEntityPersistedEvent::class => ['beforeEntityPersisted'],
            AfterEntityUpdatedEvent::class => ['afterEntityUpdated'],
            AfterEntityPersistedEvent::class => ['afterEntityPersisted'],
        ];
    }

    public function beforeEntityUpdatedEvent(BeforeEntityPersistedEvent $event): void
    {
        // Event, tags
        $entity = $event->getEntityInstance();
        // @TODO: Handle normalization of data.
    }

    public function beforeEntityPersisted(BeforeEntityPersistedEvent $event): void
    {
        // Event, tags
        $entity = $event->getEntityInstance();
        // @TODO: Handle normalization of data.
    }

    public function afterEntityUpdated(AfterEntityUpdatedEvent $event): void
    {
        $entity = $event->getEntityInstance();
        $this->handle($entity);
    }

    public function afterEntityPersisted(AfterEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();
        $this->handle($entity);
    }

    /**
     * Handling the updated entity and sends it to reindexing.
     *
     * @TODO: change into message to not lock up UI.
     *
     * @param object $entity
     * @return void
     *
     * @throws \App\Exception\NotSupportedEntityException
     */
    private function handle(object $entity): void
    {
        switch (get_class($entity)) {
            case Image::class:
            case Tag::class:
            case Address::class:
            case Location::class:
                $events = $this->eventFinder->findEvents($entity);
                foreach ($events as $event) {
                    $this->reindex($event);
                }
                break;

            case Event::class:
                $this->reindex($entity);
                break;
        }
    }

    /**
     * Re-index event by injection it into the queue system.
     *
     * @param Event $event
     *   The event to reindex.
     */
    private function reindex(Event $event): void
    {
        $eventId = $event->getId();
        if (!is_null($eventId)) {
            $this->messageBus->dispatch(new ImageMessage($eventId, $event->getImage()?->getId()));
        } else {
            // This should be impossible.
            $this->logger->error('Tried to reindex event without an ID');
        }
    }
}
