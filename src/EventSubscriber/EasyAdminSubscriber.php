<?php

namespace App\EventSubscriber;

use App\Entity\Address;
use App\Entity\Event;
use App\Entity\Image;
use App\Entity\Location;
use App\Entity\Tag;
use App\Message\ImageMessage;
use App\Service\ContentNormalizerInterface;
use App\Service\EventFinder;
use Doctrine\ORM\Mapping\Entity;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use phpDocumentor\Reflection\Types\This;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly LoggerInterface $logger,
        private readonly EventFinder $eventFinder,
        private readonly ContentNormalizerInterface $contentNormalizer,
        private readonly int $excerptMaxLength,
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

    public function beforeEntityUpdatedEvent(BeforeEntityUpdatedEvent $event): void
    {
        $entity = $event->getEntityInstance();
        $this->handleNormalization($entity);
    }

    public function beforeEntityPersisted(BeforeEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();
        $this->handleNormalization($entity);
    }

    public function afterEntityUpdated(AfterEntityUpdatedEvent $event): void
    {
        $entity = $event->getEntityInstance();
        $this->handleEntity($entity);
    }

    public function afterEntityPersisted(AfterEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();
        $this->handleEntity($entity);
    }

    /**
     * Handle normalization of content.
     *
     * @param object $entity
     *   The entity to preform normalization on
     */
    private function handleNormalization(object $entity): void
    {
        if ($entity instanceof Event) {
            $description = $entity->getDescription();
            if (!is_null($description)) {
                $entity->setDescription($this->contentNormalizer->normalize($description));
            }

            $excerpt = $entity->getExcerpt();
            if (!is_null($excerpt)) {
                $excerpt = $this->contentNormalizer->normalize($excerpt);
                $excerpt = $this->contentNormalizer->trimLength($excerpt, $this->excerptMaxLength);
                $entity->setExcerpt($excerpt);
            }
        }
    }

    /**
     * Handling the entity and sends it to reindexing.
     *
     * @TODO: change into message to not lock up UI.
     *
     * @throws \App\Exception\NotSupportedEntityException
     */
    private function handleEntity(object $entity): void
    {
        switch (get_class($entity)) {
            case Image::class:
            case Tag::class:
            case Address::class:
            case Location::class:
                $events = $this->eventFinder->findEvents($entity);
                foreach ($events as $event) {
                    $this->index($event);
                }
                break;

            case Event::class:
                $this->index($entity);
                break;
        }
    }

    /**
     * Re-index event by injection it into the queue system.
     *
     * @param Event $event
     *   The event to reindex
     */
    private function index(Event $event): void
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
