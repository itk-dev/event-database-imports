<?php

namespace App\Controller\Admin;

use App\Entity\Location;
use App\Types\UserRoles;
use Doctrine\Common\Collections\Order;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Symfony\Component\Translation\TranslatableMessage;

class LocationCrudController extends AbstractBaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return Location::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['name' => Order::Ascending->value])
            ->showEntityActionsInlined()
            ->setPageTitle('edit', new TranslatableMessage('admin.location.edit.title'))
            ->setPageTitle('index', new TranslatableMessage('admin.location.index.title'))
            ->setPageTitle('detail', new TranslatableMessage('admin.location.edit.title'));
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
                ->setLabel(new TranslatableMessage('admin.location.id'))
                ->setDisabled()
                ->hideWhenCreating(),

            FormField::addFieldset('Basic information')
                ->setLabel(new TranslatableMessage('admin.location.basic.headline')),
            TextField::new('name')
                ->setLabel(new TranslatableMessage('admin.location.basic.name')),
            AssociationField::new('address')
                ->setLabel(new TranslatableMessage('admin.location.basic.headline'))
                ->hideOnIndex(),

            FormField::addFieldset('Enriched information')
                ->setLabel(new TranslatableMessage('admin.location.enriched.headline')),
            UrlField::new('url')
                ->setLabel(new TranslatableMessage('admin.location.enriched.url')),
            EmailField::new('mail')
                ->setLabel(new TranslatableMessage('admin.location.enriched.mail')),
            UrlField::new('image')
                ->setLabel(new TranslatableMessage('admin.location.enriched.image'))
                ->hideOnIndex(),
            TelephoneField::new('telephone')
                ->setLabel(new TranslatableMessage('admin.location.enriched.telephone'))
                ->hideOnIndex(),
            BooleanField::new('disabilityAccess')
                ->setLabel(new TranslatableMessage('admin.location.enriched.disability-access'))
                ->renderAsSwitch(false),
            AssociationField::new('events')
                ->setLabel('admin.location.enriched.events')
                ->setDisabled(),

            FormField::addFieldset(new TranslatableMessage('admin.location.edited.headline'))
                ->hideWhenCreating(),
            DateTimeField::new('updated_at')
                ->setLabel(new TranslatableMessage('admin.location.edited.updated'))
                ->setDisabled()
                ->hideWhenCreating()
                ->hideOnIndex()
                ->setFormat(DashboardController::DATETIME_FORMAT),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name')
            ->add('mail')
            ->add('url')
            ->add('disabilityAccess')
            ->add('address')
        ;
    }
}
