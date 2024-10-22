<?php

namespace App\Controller\Admin;

use App\Entity\Image;
use App\Service\ImageHandlerInterface;
use App\Utils\UriHelper;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Translation\TranslatableMessage;

class EmbedImageController extends AbstractBaseCrudController
{
    public function __construct(private readonly ImageHandlerInterface $imageHandler)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Image::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title')
                ->setLabel(new TranslatableMessage('admin.image.title')),
            ImageField::new('local')
                ->setLabel(new TranslatableMessage('admin.image.local'))
                ->setUploadDir(UriHelper::UPLOAD_DIR)
                ->setUploadedFileNamePattern('[slug]-[randomhash].[extension]'),
        ];
    }

    private function getImageUrl(?string $imageUrl, string $size): ?string
    {
        if (null === $imageUrl) {
            return null;
        }

        return $this->imageHandler->getTransformedImageUrls($imageUrl)[$size];
    }
}
