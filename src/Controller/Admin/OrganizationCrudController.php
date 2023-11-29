<?php

namespace App\Controller\Admin;

use App\Entity\Organization;
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
}
