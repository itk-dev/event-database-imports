<?php

declare(strict_types=1);

namespace App\Tests\Unit\MessageHandler;

use App\Entity\Image;
use App\Exception\ImageFetchException;
use App\Message\GeocoderMessage;
use App\Message\ImageMessage;
use App\MessageHandler\ImageHandler;
use App\Repository\ImageRepository;
use App\Service\ImageServiceInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * The image step must never stop the import chain: if the image cannot be fetched, the image is left as it is and
 * the event continues to the geocoder step.
 */
#[CoversClass(ImageHandler::class)]
final class ImageHandlerTest extends TestCase
{
    public function testFetchFailureKeepsImageAndContinuesChain(): void
    {
        $image = new Image()->setSource('https://example.com/images/uploads')->setLocal('uploads/kept.png');
        $repository = $this->createStub(ImageRepository::class);
        $repository->method('findOneBy')->willReturn($image);

        $imageService = $this->createStub(ImageServiceInterface::class);
        $imageService->method('fetch')->willThrowException(new ImageFetchException('No image file in https://example.com/images/uploads'));

        $dispatched = [];
        $bus = $this->createStub(MessageBusInterface::class);
        $bus->method('dispatch')->willReturnCallback(static function (object $message) use (&$dispatched): Envelope {
            $dispatched[] = $message;

            return new Envelope($message);
        });

        $logger = new class extends AbstractLogger {
            /** @var list<string> */
            public array $messages = [];

            public function log($level, \Stringable|string $message, array $context = []): void
            {
                $this->messages[] = (string) $message;
            }
        };

        new ImageHandler($imageService, $repository, $bus, $logger)(new ImageMessage(42, 7));

        $this->assertSame('uploads/kept.png', $image->getLocal());
        $this->assertCount(1, $dispatched);
        $this->assertInstanceOf(GeocoderMessage::class, $dispatched[0]);
        $this->assertSame(42, $dispatched[0]->getEventId());
        $this->assertCount(1, $logger->messages);
        $this->assertStringContainsString('https://example.com/images/uploads', $logger->messages[0]);
    }
}
