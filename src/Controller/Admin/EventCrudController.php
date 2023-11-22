<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class EventCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Event::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['id' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->setDisabled()
                ->hideWhenCreating(),

            FormField::addFieldset('Basic information'),
            TextField::new('title'),
            TextEditorField::new('excerpt')
                ->hideOnIndex(),
            TextEditorField::new('description')
                ->hideOnIndex(),
            AssociationField::new('image')
                ->hideOnIndex(),
            AssociationField::new('tags')
                ->hideOnIndex(),

            FormField::addFieldset('Location information'),
            UrlField::new('url'),
            UrlField::new('ticketUrl'),
            AssociationField::new('location'),

            FormField::addFieldset('Edited'),
            AssociationField::new('organization'),
            DateTimeField::new('updated_at')
                ->setLabel('Last updated')
                ->setDisabled()
                ->hideWhenCreating()
                ->setFormat(DashboardController::DATETIME_FORMAT),
        ];
    }
}
