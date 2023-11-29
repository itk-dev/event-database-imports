<?php

namespace App\Controller\Admin;

use App\Entity\Image;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Symfony\Component\Translation\TranslatableMessage;

class ImageCrudController extends AbstractBaseCrudController
{
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

            TextField::new('title')
                ->setLabel(new TranslatableMessage('admin.image.title')),
            UrlField::new('source')
                ->setLabel(new TranslatableMessage('admin.image.source')),

            FormField::addFieldset('Edited')
                ->setLabel(new TranslatableMessage('admin.image.edited.headline'))
                ->hideWhenCreating(),
            DateTimeField::new('updated_at')
                ->setLabel(new TranslatableMessage('admin.image.edited.update'))
                ->setLabel('Last updated')
                ->setDisabled()
                ->hideWhenCreating()
                ->setFormat(DashboardController::DATETIME_FORMAT),
            ];
    }
}
