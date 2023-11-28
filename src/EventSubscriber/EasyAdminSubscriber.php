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
            BeforeEntityPersistedEvent::class => ['beforeEntityPersisted'],
            AfterEntityPersistedEvent::class => ['afterEntityPersisted'],
            AfterEntityUpdatedEvent::class => ['afterEntityUpdated'],
        ];
    }

    public function beforeEntityPersisted(BeforeEntityPersistedEvent $event): void
    {
        // Event, tags
        $entity = $event->getEntityInstance();
        $t = 1;
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

    // @TODO: change into message to not lock up UI.
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
