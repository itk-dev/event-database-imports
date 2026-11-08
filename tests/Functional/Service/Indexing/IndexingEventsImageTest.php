<?php

declare(strict_types=1);

namespace App\Tests\Functional\Service\Indexing;

use App\Entity\Event;
use App\Entity\Image;
use App\Service\Indexing\IndexingEvents;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * The aarhusguiden.dk client reads imageUrls.large and fails (blank event page) on "imageUrls": null. An event
 * without an image must be indexed with an empty list.
 */
#[CoversClass(IndexingEvents::class)]
final class IndexingEventsImageTest extends KernelTestCase
{
    public function testEventWithoutImageHasEmptyImageUrls(): void
    {
        $this->assertSame([], $this->serialize($this->event())['imageUrls']);
    }

    public function testImageWithoutFileHasEmptyImageUrls(): void
    {
        $event = $this->event()->setImage(new Image()->setTitle('No file'));

        $this->assertSame([], $this->serialize($event)['imageUrls']);
    }

    public function testImageHasImageUrls(): void
    {
        $event = $this->event()->setImage(new Image()->setLocal('uploads/test.png'));

        $imageUrls = $this->serialize($event)['imageUrls'];
        $this->assertIsArray($imageUrls);
        $this->assertArrayHasKey('large', $imageUrls);
        $this->assertStringEndsWith('/large/uploads/test.png', $imageUrls['large']);
    }

    private function event(): Event
    {
        $event = new Event();
        $event->setTitle('Image test')
            ->setCreatedAt(new \DateTime('2026-07-01 12:00:00', new \DateTimeZone('UTC')))
            ->setUpdatedAt(new \DateTime('2026-07-01 12:00:00', new \DateTimeZone('UTC')));

        return $event;
    }

    /**
     * @return array<string, mixed>
     */
    private function serialize(Event $event): array
    {
        $indexing = self::getContainer()->get(IndexingEvents::class);
        $this->assertInstanceOf(IndexingEvents::class, $indexing);

        return $indexing->serialize($event);
    }
}
