<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\ContentNormalizer;
use App\Tests\Utils\TestData;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(ContentNormalizer::class)]
final class ContentNormalizerTest extends KernelTestCase
{
    /**
     * @throws \Exception
     */
    public function testNormalization(): void
    {
        $service = $this->getContentNormalizerService();
        $normalized = $service->sanitize('<p>test<b>test<p><a href="http://aakb.dk/test.php"></a></p>');

        $this->assertSame('<p>test<b>test</b></p><p><b><a href="https://aakb.dk/test.php"></a></b></p>', $normalized);
    }

    /**
     * @throws \Exception
     */
    public function testTrimLength(): void
    {
        $service = $this->getContentNormalizerService();

        // Test split on word boundaries.
        $trimmed = $service->trimLength(TestData::LONG_STRING, 255);
        $this->assertLessThanOrEqual(255, mb_strlen($trimmed));
        $this->assertStringEndsWith('It', $trimmed);
        $this->assertStringEndsNotWith(' ', $trimmed);

        // Test split without limiting to word boundaries.
        $trimmed = $service->trimLength(TestData::LONG_STRING, 255, false);
        $this->assertSame(255, mb_strlen($trimmed));
        $this->assertStringEndsWith('surv', $trimmed);
        $this->assertStringEndsNotWith(' ', $trimmed);
    }

    /**
     * Helper function to bootstrap the service tested.
     *
     * @return ContentNormalizer
     *   The service
     *
     * @throws \Exception
     */
    private function getContentNormalizerService(): ContentNormalizer
    {
        self::bootKernel();
        $container = ContentNormalizerTest::getContainer();

        return $container->get(ContentNormalizer::class);
    }
}
