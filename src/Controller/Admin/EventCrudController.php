<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Entity\Organization;
use App\Service\ImageServiceInterface;
use App\Types\UserRoles;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Asset;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Dto\AssetsDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
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
        private readonly ImageServiceInterface $imageService,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Event::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['id' => Order::Descending->value])
            ->showEntityActionsInlined()
            ->setPageTitle('edit', new TranslatableMessage('admin.event.edit.title'))
            ->setPageTitle('index', new TranslatableMessage('admin.event.index.title'))
            ->setPageTitle('detail', new TranslatableMessage('admin.event.edit.title'));
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);

        if (!$this->isGranted(UserRoles::ROLE_ORGANIZATION_EDITOR->value)) {
            $actions->remove(Crud::PAGE_INDEX, Action::NEW);
        }

        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addFieldset('Basic information')
            ->setLabel(new TranslatableMessage('admin.event.basic.headline'));
        yield IdField::new('id')
            ->setLabel(new TranslatableMessage('admin.event.id'))
            ->setDisabled()
            ->hideWhenCreating();
        yield TextField::new('title')
            ->setLabel(new TranslatableMessage('admin.event.title'));
        yield TextareaField::new('excerpt')
                ->setLabel(new TranslatableMessage('admin.event.basic.excerpt'))
                ->setMaxLength(Event::EXCERPT_MAX_LENGTH)
                ->hideOnIndex();
        yield TextEditorField::new('description')
                ->setLabel(new TranslatableMessage('admin.event.basic.description'))
                ->hideOnDetail()
                ->hideOnIndex();
        yield TextareaField::new('description')
                ->setLabel(new TranslatableMessage('admin.event.basic.description'))
                ->renderAsHtml()
                ->hideOnIndex()
                ->hideOnForm();

        // Image / Detail view
        yield ImageField::new('image')
            ->setLabel(new TranslatableMessage('admin.event.admin.image.local'))
            ->formatValue(function ($value) {
                $local = $value?->getLocal();
                $transformed = null === $local ? null : $this->imageService->getTransformedImageUrls($local);

                return $transformed['large'] ?? null;
            })
        ->hideOnIndex()->hideOnForm();

        // Image / Form view
        // @see self::getFieldAssets()
        yield AssociationField::new('image')
                ->setLabel(new TranslatableMessage('admin.event.basic.image'))
                ->hideOnIndex()
                ->renderAsEmbeddedForm(EmbedImageController::class);
        yield AssociationField::new('tags')
                ->setLabel(new TranslatableMessage('admin.event.basic.tags'))
                ->hideOnDetail();
        yield ArrayField::new('tags')
                ->setLabel(new TranslatableMessage('admin.event.basic.tags'))
                ->onlyOnDetail();

        yield FormField::addFieldset('Occurrences')
                ->setLabel(new TranslatableMessage('admin.event.occurrences'));
        yield CollectionField::new('occurrences')
                ->setLabel(new TranslatableMessage('admin.event.occurrences'))
                ->hideOnIndex()
                ->renderExpanded(false)
                ->useEntryCrudForm();

        yield FormField::addFieldset('Location information')
                ->setLabel(new TranslatableMessage('admin.event.location.headline'));
        yield UrlField::new('url')
                ->setLabel(new TranslatableMessage('admin.event.location.url'))
                ->hideOnIndex();
        yield UrlField::new('ticketUrl')
                ->setLabel(new TranslatableMessage('admin.event.location.ticketUrl'))
                ->hideOnIndex();
        yield AssociationField::new('location')
                ->setLabel(new TranslatableMessage('admin.event.location.location'));

        yield FormField::addFieldset('Organizer information')
                ->setLabel(new TranslatableMessage('admin.event.organizer.headline'));

        if ($this->isGranted(UserRoles::ROLE_EDITOR->value)) {
            yield AssociationField::new('organization')
                ->setLabel(new TranslatableMessage('admin.event.edited.organization'));
        } else {
            yield AssociationField::new('organization')
                ->setLabel(new TranslatableMessage('admin.event.edited.organization'))
                ->setQueryBuilder(
                    fn (QueryBuilder $queryBuilder) => $queryBuilder
                        ->select('o')
                        ->from(Organization::class, 'o')
                        ->where(':user MEMBER OF o.users')
                        ->setParameter('user', $this->getUser())
                );
        }
        yield AssociationField::new('partners')
                ->setLabel(new TranslatableMessage('admin.event.edited.partners'))
                ->hideOnDetail();
        yield ArrayField::new('partners')
            ->setLabel(new TranslatableMessage('admin.event.edited.partners'))
            ->onlyOnDetail();

        yield FormField::addFieldset('Edited')
                ->setLabel(new TranslatableMessage('admin.event.edited.headline'))
                ->hideWhenCreating();
        yield AssociationField::new('feed')
                ->setLabel(new TranslatableMessage('admin.event.edited.feed'))
                ->hideOnForm()
                ->hideOnIndex();
        yield DateTimeField::new('updated_at')
                ->setLabel(new TranslatableMessage('admin.event.edited.updated'))
                ->setDisabled()
                ->hideWhenCreating();
    }

    public function configureFilters(Filters $filters): Filters
    {
        if ($this->isGranted(UserRoles::ROLE_EDITOR->value)) {
            $filters->add('feed');
        }

        // 'organization' filter has additional config when added in MyEventCrudController
        $filters->add('organization');

        return $this->configureBaseFilters($filters);
    }

    protected function configureBaseFilters(Filters $filters): Filters
    {
        return $filters
            ->add('id')
            ->add('partners')
            ->add('location')
            ->add('tags')
            ->add('title')
            ->add('url')
            ->add('ticketUrl')
        ;
    }

    /**
     * Override parent to add image and upload fields from "EmbedImageController".
     *
     * EasyAdmin dynamically adds the relevant js assets to the html head section
     * for the fields on the page. However, when using "renderAsEmbeddedForm", js
     * for the fields in that controller is not added, so we have to that manually.
     */
    protected function getFieldAssets(FieldCollection $fieldDtos): AssetsDto
    {
        $fieldAssetsDto = parent::getFieldAssets($fieldDtos);

        $imageAssetDto = Asset::new('field-image.js')->getAsDto();
        $imageAssetDto->setPackageName('easyadmin.assets.package');
        $imageAssetDto->setLoadedOn(KeyValueStore::new(['new' => 'new', 'edit' => 'edit']));
        $fieldAssetsDto->addJsAsset($imageAssetDto);

        $uploadAssetDto = Asset::new('field-file-upload.js')->getAsDto();
        $uploadAssetDto->setPackageName('easyadmin.assets.package');
        $uploadAssetDto->setLoadedOn(KeyValueStore::new(['new' => 'new', 'edit' => 'edit']));
        $fieldAssetsDto->addJsAsset($uploadAssetDto);

        return $fieldAssetsDto;
    }
}
