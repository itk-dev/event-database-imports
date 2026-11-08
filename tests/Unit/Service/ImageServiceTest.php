<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Image;
use App\Service\ImageService;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Message\WarmupCache;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Thumbnail warm-up must only be requested for images that exist on disk. An image whose local path
 * is the upload folder (or a missing file) made the WarmupCache handler fail 3 times per message in prod.
 */
#[CoversClass(ImageService::class)]
final class ImageServiceTest extends TestCase
{
    private string $publicPath;

    protected function setUp(): void
    {
        $this->publicPath = sys_get_temp_dir().'/image-service-test-'.uniqid().'/';
        mkdir($this->publicPath.'uploads', 0777, true);
        touch($this->publicPath.'uploads/existing.png');
    }

    public function testWarmsUpExistingFile(): void
    {
        $this->assertSame(['uploads/existing.png'], $this->transform('uploads/existing.png'));
    }

    public function testSkipsUploadFolder(): void
    {
        $this->assertSame([], $this->transform('/uploads/'));
    }

    public function testSkipsMissingFile(): void
    {
        $this->assertSame([], $this->transform('uploads/missing.png'));
    }

    /**
     * @return list<string> the paths of the dispatched WarmupCache messages
     */
    private function transform(string $local): array
    {
        $dispatched = [];
        $bus = $this->createStub(MessageBusInterface::class);
        $bus->method('dispatch')->willReturnCallback(static function (object $message) use (&$dispatched): Envelope {
            if ($message instanceof WarmupCache) {
                $dispatched[] = $message->getPath();
            }

            return new Envelope($message);
        });

        $service = new ImageService(
            new MockHttpClient(),
            $this->publicPath,
            'https://example.com',
            ['image/png'],
            $bus,
            $this->createStub(CacheManager::class),
            $this->createStub(FilterManager::class),
        );
        $service->transform(new Image()->setLocal($local));

        return $dispatched;
    }
}
