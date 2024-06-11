<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Exception\ImageMineTypeException;
use App\Service\ImageHandler;
use App\Tests\Utils\PhpUnitUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(ImageHandler::class)]
final class ImageHandlerTest extends KernelTestCase
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
     * @throws \Exception
     */
    public function testGenerateLocalFilename()
    {
        $filename = PhpUnitUtils::callPrivateMethod(
            $this->getImageService(),
            'generateLocalFilename',
            ['https://bora-bora.dk/wp-content/uploads/2023/06/DansBabyDans-Web-Main-2600x1500px-scaled.jpg?size=big&color=true', 'image/jpeg']
        );
        $this->assertEquals('ea21c870585e2cf8c79b9165e714ea9aa96257168fd12dc9fae74d98b188de6f.jpg', $filename);
        $this->assertStringEndsWith('.jpg', $filename);

        $filename = PhpUnitUtils::callPrivateMethod(
            $this->getImageService(),
            'generateLocalFilename',
            ['https://bora-bora.dk/wp-content/uploads/2023/06/DansBabyDans-Wesdfaæøåb-Mai.png', 'image/png']
        );
        $this->assertEquals('1d5b63fe3bfd9c155df8820dd756af8b4b7cd643c2b35b08c9e02362222f079a.png', $filename);
        $this->assertStringEndsWith('.png', $filename);

        $filename2 = PhpUnitUtils::callPrivateMethod(
            $this->getImageService(),
            'generateLocalFilename',
            ['https://bora-bora.dk/wp-content/uploads/2023/06/DansBabyDans-Wesdfaæøåb-Mai.png&test=big', 'image/png']
        );
        $this->assertNotEquals($filename, $filename2);
    }

    /**
     * @throws \Exception
     */
    public function testMineTypesException()
    {
        $this->expectException(ImageMineTypeException::class);
        PhpUnitUtils::callPrivateMethod(
            $this->getImageService(),
            'generateLocalFilename',
            ['https://bora-bora.dk/wp-content/uploads/2023/06/DansBabyDans-Web-Main-2600x1500px-scaled.jpg?size=big&color=true', 'image/gif']
        );
    }

    /**
     * Helper function to bootstrap the service tested.
     *
     * @return ImageHandler
     *   The service
     *
     * @throws \Exception
     */
    private function getImageService(): ImageHandler
    {
        self::bootKernel();
        $container = ImageHandlerTest::getContainer();

        return $container->get(ImageHandler::class);
    }
}
