<?php

namespace App\Controller\Admin;

use App\Entity\Feed;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Translation\TranslatableMessage;

class FeedCrudController extends AbstractBaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return Feed::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->setLabel(new TranslatableMessage('admin.feed.id'))
                ->setDisabled()
                ->hideWhenCreating(),

            TextField::new('name')
                ->setLabel(new TranslatableMessage('admin.feed.name')),
            CodeEditorField::new('configurationField')
                ->setLabel(new TranslatableMessage('admin.feed.configuration'))
                ->setLanguage('js')
                ->hideOnIndex(),

            FormField::addFieldset(new TranslatableMessage('admin.feed.edited.headline'))
                ->hideWhenCreating(),
            DateTimeField::new('last_read')
                ->setLabel(new TranslatableMessage('admin.feed.edited.last_read'))
                ->setDisabled()
                ->hideWhenCreating()
                ->setFormat(DashboardController::DATETIME_FORMAT),

            DateTimeField::new('updated_at')
                ->setLabel(new TranslatableMessage('admin.feed.edited.update'))
                ->setDisabled()
                ->hideWhenCreating()
                ->setFormat(DashboardController::DATETIME_FORMAT),
        ];
    }
}
