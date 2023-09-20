<?php

declare(strict_types=1);

namespace App\Tests;

use App\Services\Image;
use App\Tests\Utils\PhpUnitUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(Image::class)]
final class ImageServiceTest extends KernelTestCase
{
    /**
     * @throws \Exception
     */
    public function testCreatePath()
    {
        $depth = 2;
        $size = 10;
        $path = PhpUnitUtils::callPrivateMethod(
            $this->getImageService(),
            'generatePath',
            ['https://bora-bora.dk/wp-content/uploads/2023/06/DansBabyDans-Web-Main-2600x1500px-scaled.jpg', false, $depth, $size]
        );

        $this->assertEquals($depth * $size + 2, strlen($path));
        $this->assertStringEndsWith('/', $path);
    }

    /**
     * Helper function to bootstrap the service tested.
     *
     * @return Image
     *   The service
     *
     * @throws \Exception
     */
    private function getImageService(): Image
    {
        self::bootKernel();
        $container = ImageServiceTest::getContainer();

        return $container->get(Image::class);
    }
}
