<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Entity\Organization;
use App\Service\ImageHandlerInterface;
use App\Types\UserRoles;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
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
        private readonly ImageHandlerInterface $imageHandler,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Event::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud->setDefaultSort(['id' => Order::Descending->value]);
        $crud->showEntityActionsInlined();

        return $crud;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            FormField::addFieldset('Basic information')
                ->setLabel(new TranslatableMessage('admin.event.basic.headline')),
            IdField::new('id', 'admin.event.id'),
            TextField::new('title'),
            ImageField::new('image')
                ->setLabel(new TranslatableMessage('admin.event.admin.image.local'))
                ->formatValue(function ($value) {
                    $local = $value?->getLocal();
                    $transformed = null === $local ? null : $this->imageHandler->getTransformedImageUrls($local);

                    return $transformed['large'] ?? null;
                })->hideOnIndex()->hideOnForm(),
            TextareaField::new('excerpt')
                ->setLabel(new TranslatableMessage('admin.event.basic.excerpt'))
                ->setMaxLength(Event::EXCERPT_MAX_LENGTH)
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
                ->hideOnIndex()
                ->renderAsEmbeddedForm(EmbedImageController::class),
            AssociationField::new('tags')
                ->setLabel(new TranslatableMessage('admin.event.basic.tags'))
                ->hideOnIndex(),

            FormField::addFieldset('Occurrences')
                ->setLabel(new TranslatableMessage('admin.event.occurrences')),
            CollectionField::new('occurrences')
                ->hideOnIndex()
                ->renderExpanded(false)
                ->useEntryCrudForm(),

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

            FormField::addFieldset('Organizer information')
                ->setLabel(new TranslatableMessage('admin.event.organizer.headline')),
            AssociationField::new('organization')
                ->setLabel(new TranslatableMessage('admin.event.edited.organization'))
                ->setQueryBuilder(
                    fn (QueryBuilder $queryBuilder) => $queryBuilder
                        ->select('o')
                        ->from(Organization::class, 'o')
                        ->where(':user MEMBER OF o.users')
                        ->setParameter('user', $this->getUser())
                ),
            AssociationField::new('partners')
                ->setLabel(new TranslatableMessage('admin.event.edited.partners')),

            FormField::addFieldset('Edited')
                ->setLabel(new TranslatableMessage('admin.event.edited.headline'))
                ->hideWhenCreating(),
            AssociationField::new('feed')
                ->setLabel(new TranslatableMessage('admin.event.edited.feed'))
                ->hideOnForm()
                ->hideOnIndex(),
            DateTimeField::new('updated_at')
                ->setLabel(new TranslatableMessage('admin.event.edited.updated'))
                ->setDisabled()
                ->hideWhenCreating(),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        if ($this->isGranted(UserRoles::ROLE_EDITOR->value)) {
            $filters->add('organization');
        }

        return $filters
            ->add('location')
            ->add('title')
        ;
    }

    protected function getOrganizationChoices(): array
    {
        $choices = [];
        foreach ($this->getUser()->getOrganizations() as $organization) {
            $choices[$organization->getName()] = $organization->getId();
        }

        return $choices;
    }
}
