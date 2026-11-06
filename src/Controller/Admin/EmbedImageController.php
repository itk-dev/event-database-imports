<?php

namespace App\Controller\Admin;

use App\Entity\Image;
use App\Service\ImageServiceInterface;
use App\Utils\UriHelper;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\Image as ImageConstraint;

class EmbedImageController extends AbstractBaseCrudController
{
    /**
     * @param list<non-empty-string> $allowedMimeTypes
     */
    public function __construct(
        private readonly ImageServiceInterface $imageService,
        #[Autowire('%env(json:ALLOWED_IMAGE_MIME_TYPES)%')]
        private readonly array $allowedMimeTypes,
    ) {
    }

    #[\Override]
    public static function getEntityFqcn(): string
    {
        return Image::class;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title')
                ->setLabel(new TranslatableMessage('admin.image.title')),
            ImageField::new('local')
                ->setLabel(new TranslatableMessage('admin.image.local'))
                ->setUploadDir(UriHelper::UPLOAD_DIR)
                ->setUploadedFileNamePattern('[slug]-[randomhash].[extension]')
                // Same types as feed images, and reject files the image library cannot decode.
                ->setFileConstraints(new ImageConstraint(mimeTypes: $this->allowedMimeTypes, detectCorrupted: true)),
        ];
    }

    private function getImageUrl(?string $imageUrl, string $size): ?string
    {
        if (null === $imageUrl) {
            return null;
        }

        return $this->imageService->getTransformedImageUrls($imageUrl)[$size];
    }
}
