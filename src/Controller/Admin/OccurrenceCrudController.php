<?php

namespace App\Controller\Admin;

use App\Entity\Occurrence;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class OccurrenceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Occurrence::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['start' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->setDisabled()
                ->hideWhenCreating(),

            AssociationField::new('event')
                ->setLabel('Link to event')
                ->autocomplete(),

            FormField::addFieldset('Dates'),
            DateTimeField::new('start')
                ->setColumns(2)
                ->setLabel('Start time'),
            DateTimeField::new('end')
                ->setColumns(2)
                ->setLabel('End time'),

            FormField::addFieldset('Basic information'),
            TextField::new('ticketPriceRange'),
            TextField::new('room'),

            FormField::addFieldset('Edited'),
            DateTimeField::new('updated_at')
                ->setLabel('Last updated')
                ->setDisabled()
                ->hideWhenCreating()
                ->setFormat(DashboardController::DATETIME_FORMAT),
        ];
    }
}
