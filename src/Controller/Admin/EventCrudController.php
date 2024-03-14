<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Service\ImageHandlerInterface;
use Doctrine\Common\Collections\Criteria;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Symfony\Component\Translation\TranslatableMessage;

class EventCrudController extends AbstractBaseCrudController
{
    public function __construct(
        protected readonly int $excerptMaxLength,
        private readonly ImageHandlerInterface $imageHandler
    ) {
    }

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
            TextField::new('title'),
            ImageField::new('image')
                ->setLabel(new TranslatableMessage('admin.event.admin.image.local'))
                ->formatValue(function ($value) {
                    return $this->imageHandler->getTransformedImageUrls($value->getLocal())['large'];
                }
                )->hideOnIndex()->hideOnForm(),
            TextareaField::new('excerpt')
                ->setLabel(new TranslatableMessage('admin.event.basic.excerpt'))
                ->setMaxLength($this->excerptMaxLength)
                ->hideOnIndex(),
            TextEditorField::new('description')
                ->setLabel(new TranslatableMessage('admin.event.basic.description'))
                ->hideOnDetail()
                ->hideOnIndex(),
            TextareaField::new('description')
                ->setLabel(new TranslatableMessage('admin.event.basic.description'))
                ->renderAsHtml()
                ->hideOnIndex()
                ->hideOnForm(),
            AssociationField::new('image')
                ->setLabel(new TranslatableMessage('admin.event.basic.image'))
                ->hideOnIndex(),
            AssociationField::new('tags')
                ->setLabel(new TranslatableMessage('admin.event.basic.tags'))
                ->hideOnIndex(),

            FormField::addFieldset('Occurrences')
                ->setLabel(new TranslatableMessage('admin.event.occurrences')),
            AssociationField::new('occurrences')
                ->hideOnIndex(),
            AssociationField::new('dailyOccurrences')
                ->hideOnIndex(),

            FormField::addFieldset('Location information')
                ->setLabel(new TranslatableMessage('admin.event.location.headline')),
            UrlField::new('url')
                ->setLabel(new TranslatableMessage('admin.event.location.url'))
                ->hideOnIndex(),
            UrlField::new('ticketUrl')
                ->setLabel(new TranslatableMessage('admin.event.location.ticketUrl'))
                ->hideOnIndex(),
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

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('organization')
            ->add('location')
            ->add('title')
        ;
    }
}
