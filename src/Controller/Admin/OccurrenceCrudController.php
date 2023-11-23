<?php

namespace App\Controller\Admin;

use App\Entity\Occurrence;
use Doctrine\Common\Collections\Criteria;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Translation\TranslatableMessage;

class OccurrenceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Occurrence::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['start' => Criteria::ASC]);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->setLabel(new TranslatableMessage('admin.occurrence.id'))
                ->setDisabled()
                ->hideWhenCreating(),

            AssociationField::new('event')
                ->setLabel(new TranslatableMessage('admin.occurrence.event-link'))
                ->autocomplete(),

            FormField::addFieldset('Dates')
                ->setLabel(new TranslatableMessage('admin.occurrence.dates.headline')),
            DateTimeField::new('start')
                ->setLabel(new TranslatableMessage('admin.occurrence.dates.start'))
                ->setColumns(2)
                ->setLabel('Start time'),
            DateTimeField::new('end')
                ->setLabel(new TranslatableMessage('admin.occurrence.dates.end'))
                ->setColumns(2)
                ->setLabel('End time'),

            FormField::addFieldset('Basic information')
                ->setLabel(new TranslatableMessage('admin.occurrence.basic.headline')),
            TextField::new('ticketPriceRange')
                ->setLabel(new TranslatableMessage('admin.occurrence.basic.price')),
            TextField::new('room')
                ->setLabel(new TranslatableMessage('admin.occurrence.basic.room')),

            FormField::addFieldset(new TranslatableMessage('admin.occurrence.edited.headline'))
                ->hideWhenCreating(),
            DateTimeField::new('updated_at')
                ->setLabel(new TranslatableMessage('admin.occurrence.edited.updated'))
                ->setDisabled()
                ->hideWhenCreating()
                ->setFormat(DashboardController::DATETIME_FORMAT),
        ];
    }
}
