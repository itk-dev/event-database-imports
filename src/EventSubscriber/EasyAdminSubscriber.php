<?php

namespace App\EventSubscriber;

use App\Entity\Address;
use App\Entity\Event;
use App\Entity\Image;
use App\Entity\Location;
use App\Entity\Tag;
use App\Message\ImageMessage;
use App\Service\Finder;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly Finder $finder,
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

    private function handle(object $entity)
    {
        switch (get_class($entity)) {
            case Image::class:
                // Update/download image and find all events using that image and reindex the events.

                break;

            case Tag::class:
                // Find all events with that tag and reindex
                $events = $this->finder->search(Tag::class, $entity);
                foreach ($events as $event) {
                    $this->reindex($event);
                }
                break;

            case Address::class:
                // Find all locations using this address and update all events using that location.

                break;

            case Location::class:
                // Find all events using this location.

            case Event::class:
                // We need to send create/updated events into the message processing queue, and we hook into the feed event
                // processing at image processing.

                // $this->messageBus->dispatch(new ImageMessage($entity->getId(), $entity->getImage()?->getId()));
                break;
        }
    }

    private function reindex(Event $event): void
    {
        $this->messageBus->dispatch(new ImageMessage($event->getId(), $event->getImage()?->getId()));
    }
}
