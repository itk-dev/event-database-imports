<?php

namespace App\Controller\Admin;

use App\Entity\Location;
use Doctrine\Common\Collections\Criteria;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
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

class LocationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Location::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['id' => Criteria::DESC]);
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
                ->setLabel(new TranslatableMessage('admin.location.enriched.disability-access')),

            FormField::addFieldset(new TranslatableMessage('admin.location.edited.headline'))
                ->hideWhenCreating(),
            DateTimeField::new('updated_at')
                ->setLabel(new TranslatableMessage('admin.location.edited.updated'))
                ->setDisabled()
                ->hideWhenCreating()
                ->setFormat(DashboardController::DATETIME_FORMAT),
        ];
    }
}
