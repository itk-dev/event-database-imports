<?php

namespace App\Controller\Admin;

use App\Entity\Address;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Translation\TranslatableMessage;

class AddressCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Address::class;
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
                ->setLabel(new TranslatableMessage('admin.address.id'))
                ->setDisabled()
                ->hideWhenCreating(),

            FormField::addFieldset(new TranslatableMessage('admin.address.headline')),
            TextField::new('street')
                ->setLabel(new TranslatableMessage('admin.address.street')),
            TextField::new('suite')
                ->setLabel(new TranslatableMessage('admin.address.suite')),
            TextField::new('city')
                ->setLabel(new TranslatableMessage('admin.address.city')),
            TextField::new('postalCode')
                ->setLabel(new TranslatableMessage('admin.address.postalCode')),
            TextField::new('country')
                ->setLabel(new TranslatableMessage('admin.address.country')),
            TextField::new('region')
                ->setLabel(new TranslatableMessage('admin.address.region')),

            FormField::addFieldset(new TranslatableMessage('admin.address.location.headline')),
            NumberField::new('latitude')
                ->setLabel(new TranslatableMessage('admin.address.location.latitude'))
                ->setNumDecimals(8)
                ->setColumns(2)
                ->hideOnIndex(),
            NumberField::new('longitude')
                ->setLabel(new TranslatableMessage('admin.address.location.longitude'))
                ->setNumDecimals(8)
                ->setColumns(2)
                ->hideOnIndex(),

            FormField::addFieldset(new TranslatableMessage('admin.address.edited.headline'))
                ->hideWhenCreating(),
            DateTimeField::new('updated_at')
                ->setLabel(new TranslatableMessage('admin.address.edited.updated'))
                ->setDisabled()
                ->hideWhenCreating()
                ->setFormat(DashboardController::DATETIME_FORMAT),
        ];
    }
}
