<?php

declare(strict_types=1);

namespace App\Tests;

use App\Services\ContentNormalizer;
use App\Tests\Utils\TestData;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(ContentNormalizer::class)]
final class ContentNormalizationTest extends KernelTestCase
{
    /**
     * @throws \Exception
     */
    public function testNormalization()
    {
        $service = $this->getContentNormalizerService();
        $normalized = $service->normalize('<p>test<b>test<p><a href="http://aakb.dk/test.php"></a></p>');

        $this->assertEquals('<p>test<b>test</b></p><p><a href="https://aakb.dk/test.php"></a></p>', $normalized);
    }

    /**
     * @throws \Exception
     */
    public function testTrimLength()
    {
        $service = $this->getContentNormalizerService();

        // Test split on word boundaries.
        $trimmed = $service->trimLength(TestData::LONG_STRING, 255);
        $this->assertLessThanOrEqual(255, mb_strlen($trimmed));
        $this->assertStringEndsWith('It', $trimmed);
        $this->assertStringEndsNotWith(' ', $trimmed);

        // Test split without limiting to word boundaries.
        $trimmed = $service->trimLength(TestData::LONG_STRING, 255, false);
        $this->assertEquals(255, mb_strlen($trimmed));
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
        $container = ContentNormalizationTest::getContainer();

        return $container->get(ContentNormalizer::class);
    }
}
