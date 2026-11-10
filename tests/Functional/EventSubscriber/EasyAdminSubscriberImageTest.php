<?php

declare(strict_types=1);

namespace App\Tests\Functional\EventSubscriber;

use App\Entity\Event;
use App\Entity\Feed;
use App\Entity\Image;
use App\EventSubscriber\EasyAdminSubscriber;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * How saving an event in the admin treats its image.
 */
#[CoversClass(EasyAdminSubscriber::class)]
final class EasyAdminSubscriberImageTest extends KernelTestCase
{
    public function testImageFileSetsSource(): void
    {
        $event = new Event()->setImage(new Image()->setLocal('uploads/test.png'));

        $this->subscriber()->beforeEntityUpdatedEvent(new BeforeEntityUpdatedEvent($event));

        $this->assertStringEndsWith('/images/uploads/test.png', (string) $event->getImage()?->getSource());
    }

    /**
     * Deleting the image file in the admin must detach the image, so the image import does not restore it from its
     * source.
     */
    public function testDeletedImageFileDetachesImage(): void
    {
        $event = new Event()->setImage(new Image()->setLocal(null)->setSource('https://example.com/images/uploads/deleted.png'));

        $this->subscriber()->beforeEntityUpdatedEvent(new BeforeEntityUpdatedEvent($event));

        $this->assertNull($event->getImage());
    }

    /**
     * A new event saved with an empty image form (a title, but no file) gets no image.
     */
    public function testNewEventWithoutImageFileHasNoImage(): void
    {
        $event = new Event()->setImage(new Image()->setTitle('No file'));

        $this->subscriber()->beforeEntityPersisted(new BeforeEntityPersistedEvent($event));

        $this->assertNull($event->getImage());
    }

    /**
     * A feed event keeps its image, even without a file: the image import may not have fetched it yet, or the
     * download failed and is retried on the next import. Feed events are read-only in the admin (ADR 007).
     */
    public function testFeedEventKeepsImageWithoutFile(): void
    {
        $image = new Image()->setSource('https://feed.example.com/image.jpg');
        $event = new Event()->setFeed(new Feed())->setImage($image);

        $this->subscriber()->beforeEntityUpdatedEvent(new BeforeEntityUpdatedEvent($event));

        $this->assertSame($image, $event->getImage());
        $this->assertSame('https://feed.example.com/image.jpg', $image->getSource());
    }

    private function subscriber(): EasyAdminSubscriber
    {
        $subscriber = self::getContainer()->get(EasyAdminSubscriber::class);
        $this->assertInstanceOf(EasyAdminSubscriber::class, $subscriber);

        return $subscriber;
    }
}
