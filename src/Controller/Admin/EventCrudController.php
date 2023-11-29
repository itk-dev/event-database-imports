<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use Doctrine\Common\Collections\Criteria;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Symfony\Component\Translation\TranslatableMessage;

class EventCrudController extends AbstractBaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return Event::class;
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
                ->setLabel(new TranslatableMessage('admin.event.id'))
                ->setDisabled()
                ->hideWhenCreating(),

            FormField::addFieldset('Basic information')
                ->setLabel(new TranslatableMessage('admin.event.basic.headline')),
            TextField::new('title')
                ->setLabel(new TranslatableMessage('admin.event.basic.title')),
            TextEditorField::new('excerpt')
                ->setLabel(new TranslatableMessage('admin.event.basic.excerpt'))
                ->hideOnIndex(),
            TextEditorField::new('description')
                ->setLabel(new TranslatableMessage('admin.event.basic.description'))
                ->hideOnIndex(),
            AssociationField::new('image')
                ->setLabel(new TranslatableMessage('admin.event.basic.image'))
                ->hideOnIndex(),
            AssociationField::new('tags')
                ->setLabel(new TranslatableMessage('admin.event.basic.tags'))
                ->hideOnIndex(),

            FormField::addFieldset('Location information')
                ->setLabel(new TranslatableMessage('admin.event.location.headline')),
            UrlField::new('url')
                ->setLabel(new TranslatableMessage('admin.event.location.url')),
            UrlField::new('ticketUrl')
                ->setLabel(new TranslatableMessage('admin.event.location.ticketUrl')),
            AssociationField::new('location')
                ->setLabel(new TranslatableMessage('admin.event.location.location')),

            FormField::addFieldset('Edited')
                ->setLabel(new TranslatableMessage('admin.event.edited.headline')),
            AssociationField::new('organization')
                ->setLabel(new TranslatableMessage('admin.event.edited.organization')),
            DateTimeField::new('updated_at')
                ->setLabel(new TranslatableMessage('admin.event.edited.updated'))
                ->setDisabled()
                ->hideWhenCreating()
                ->setFormat(DashboardController::DATETIME_FORMAT),
        ];
    }
}
