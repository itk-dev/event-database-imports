<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Controller\Admin\EmbedImageController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Admin image uploads must only accept images the production image library
 * (GD on the alpine image, no AVIF) can decode; otherwise every thumbnail
 * request for the image fails with a 500.
 */
#[CoversClass(EmbedImageController::class)]
final class EmbedImageUploadValidationTest extends KernelTestCase
{
    public function testAcceptsPng(): void
    {
        $this->assertCount(0, $this->validate($this->png()));
    }

    public function testRejectsAvif(): void
    {
        $image = imagecreatetruecolor(10, 10);
        ob_start();
        imageavif($image);

        $this->assertGreaterThan(0, \count($this->validate($this->file((string) ob_get_clean(), 'avif'))));
    }

    public function testRejectsUndecodableImage(): void
    {
        // A PNG header with the image data cut off: detected as image/png, but cannot be decoded.
        $truncated = substr((string) file_get_contents($this->png()->getPathname()), 0, 40);

        $this->assertGreaterThan(0, \count($this->validate($this->file($truncated, 'png'))));
    }

    private function validate(File $file): \Symfony\Component\Validator\ConstraintViolationListInterface
    {
        self::bootKernel();
        $controller = self::getContainer()->get(EmbedImageController::class);
        $this->assertInstanceOf(EmbedImageController::class, $controller);

        foreach ($controller->configureFields('new') as $field) {
            if ($field instanceof ImageField && 'local' === $field->getAsDto()->getProperty()) {
                /** @var list<Constraint> $constraints */
                $constraints = $field->getAsDto()->getCustomOption(ImageField::OPTION_FILE_CONSTRAINTS);
                $validator = self::getContainer()->get(ValidatorInterface::class);
                $this->assertInstanceOf(ValidatorInterface::class, $validator);

                return $validator->validate($file, $constraints);
            }
        }

        $this->fail('The "local" image field was not found');
    }

    private function png(): File
    {
        $image = imagecreatetruecolor(10, 10);
        ob_start();
        imagepng($image);

        return $this->file((string) ob_get_clean(), 'png');
    }

    private function file(string $content, string $extension): File
    {
        $path = tempnam(sys_get_temp_dir(), 'upload').'.'.$extension;
        file_put_contents($path, $content);

        return new File($path);
    }
}
