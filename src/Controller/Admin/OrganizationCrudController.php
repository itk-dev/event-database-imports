<?php

namespace App\Controller\Admin;

use App\Entity\Organization;
use App\Types\UserRoles;
use Doctrine\Common\Collections\Order;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Symfony\Component\Translation\TranslatableMessage;

class OrganizationCrudController extends AbstractBaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return Organization::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud->setDefaultSort(['name' => Order::Ascending->value]);
        $crud->showEntityActionsInlined();

        return $crud;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);

        if (!$this->isGranted(UserRoles::ROLE_EDITOR->value)) {
            $actions->remove(Crud::PAGE_INDEX, Action::NEW);
        }

        return $actions;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->setLabel(new TranslatableMessage('admin.organization.id'))
                ->setDisabled()
                ->hideWhenCreating(),

            TextField::new('name')
                ->setLabel(new TranslatableMessage('admin.organization.name')),
            EmailField::new('mail')
                ->setLabel(new TranslatableMessage('admin.organization.mail')),
            UrlField::new('url')
                ->setLabel(new TranslatableMessage('admin.organization.url')),

            FormField::addFieldset(new TranslatableMessage('admin.organization.edited.headline'))
                ->hideWhenCreating(),
            DateTimeField::new('updated_at')
                ->setLabel(new TranslatableMessage('admin.organization.edited.updated'))
                ->setDisabled()
                ->hideWhenCreating()
                ->setFormat(DashboardController::DATETIME_FORMAT),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name')
            ->add('mail')
            ->add('url')
        ;
    }
}
