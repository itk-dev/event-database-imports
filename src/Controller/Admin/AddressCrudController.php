<?php

namespace App\Controller\Admin;

use App\Entity\Address;
use App\Types\UserRoles;
use Doctrine\Common\Collections\Order;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Translation\TranslatableMessage;

class AddressCrudController extends AbstractBaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return Address::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['street' => Order::Ascending->value])
            ->showEntityActionsInlined()
            ->setPageTitle('edit', new TranslatableMessage('admin.address.edit.title'))
            ->setPageTitle('index', new TranslatableMessage('admin.address.index.title'))
            ->setPageTitle('detail', new TranslatableMessage('admin.address.edit.title'));
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);

        if (!$this->isGranted(UserRoles::ROLE_ORGANIZATION_ADMIN->value)) {
            $actions->remove(Crud::PAGE_INDEX, Action::NEW);
            $actions->remove(Crud::PAGE_DETAIL, Action::DELETE);
        }

        return $actions;
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
            TextField::new('city')
                ->setLabel(new TranslatableMessage('admin.address.city')),
            TextField::new('postalCode')
                ->setLabel(new TranslatableMessage('admin.address.postalCode')),
            TextField::new('country')
                ->setLabel(new TranslatableMessage('admin.address.country'))
                ->hideOnIndex(),
            TextField::new('region')
                ->setLabel(new TranslatableMessage('admin.address.region'))
                ->hideOnIndex(),
            AssociationField::new('locations')
                ->setLabel(new TranslatableMessage('admin.address.locations'))
                ->setDisabled(),

            FormField::addFieldset(new TranslatableMessage('admin.address.location.headline')),
            TextField::new('coordinates')
                ->setLabel(new TranslatableMessage('admin.address.location.coordinates'))
                ->hideOnDetail()
                ->hideOnForm(),
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
                ->hideOnIndex()
                ->setFormat(DashboardController::DATETIME_FORMAT),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('street')
            ->add('city')
            ->add('postalCode')
        ;
    }
}
