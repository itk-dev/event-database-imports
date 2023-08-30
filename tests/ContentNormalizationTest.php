<?php

declare(strict_types=1);

use App\Services\ContentNormalizer;
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

        $this->assertEquals('<p>test<b>test</b></p><p><a href="https://aakb.dk/test.php"></a></p>', $service->normalize('<p>test<b>test<p><a href="http://aakb.dk/test.php"></a></p>'));
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
