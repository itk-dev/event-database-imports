<?php

namespace App\Controller\Admin;

use App\Entity\Feed;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class FeedCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Feed::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->setDisabled()
                ->hideWhenCreating(),

            TextField::new('name'),
            CodeEditorField::new('configurationField')
                ->setLanguage('js')
                ->hideOnIndex(),

            FormField::addFieldset('Edited')
                ->hideWhenCreating(),
            DateTimeField::new('last_read')
                ->setLabel('Last parsed')
                ->setDisabled()
                ->hideWhenCreating()
                ->setFormat(DashboardController::DATETIME_FORMAT),

            DateTimeField::new('updated_at')
                ->setLabel('Last updated')
                ->setDisabled()
                ->hideWhenCreating()
                ->setFormat(DashboardController::DATETIME_FORMAT),
        ];
    }
}
