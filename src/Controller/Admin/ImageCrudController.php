<?php

namespace App\Controller\Admin;

use App\Entity\Image;
use App\Service\ImageHandlerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Symfony\Component\Translation\TranslatableMessage;

class ImageCrudController extends AbstractBaseCrudController
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
            IdField::new('id')
                ->setLabel(new TranslatableMessage('admin.image.id'))
                ->setDisabled()
                ->hideWhenCreating(),

            AssociationField::new('event')
                ->setLabel(new TranslatableMessage('admin.image.event')),
            TextField::new('title')
                ->setLabel(new TranslatableMessage('admin.image.title')),
            ImageField::new('local')
                ->setLabel(new TranslatableMessage('admin.image.local'))
                ->formatValue(function ($value) {
                    return $this->getImageUrl($value, 'large');
                }
                )->hideOnIndex()->hideOnForm(),
            ImageField::new('local')
                ->setLabel(new TranslatableMessage('admin.image.local'))
                ->formatValue(function ($value) {
                    return $this->getImageUrl($value, 'small');
                }
                )->hideOnForm()->hideOnDetail(),
            UrlField::new('source')
                ->setLabel(new TranslatableMessage('admin.image.source'))
                ->hideOnIndex(),

            FormField::addFieldset('Edited')
                ->setLabel(new TranslatableMessage('admin.image.edited.headline'))
                ->hideWhenCreating(),
            DateTimeField::new('updated_at')
                ->setLabel(new TranslatableMessage('admin.image.edited.update'))
                ->setLabel('Last updated')
                ->setDisabled()
                ->hideWhenCreating()
                ->hideOnIndex()
                ->setFormat(DashboardController::DATETIME_FORMAT),
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
