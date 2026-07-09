<?php

namespace App\Controller\Admin;

use App\Entity\Feed;
use App\Service\Feeds\Reader\FeedReader;
use App\Service\Feeds\Reader\FeedReaderInterface;
use App\Types\UserRoles;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\Json;

class FeedCrudController extends AbstractBaseCrudController
{
    public function __construct(
        private readonly FeedReader $feedReader,
    ) {
    }

    #[\Override]
    public static function getEntityFqcn(): string
    {
        return Feed::class;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
        ;
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);

        $actions->setPermission(Action::INDEX, UserRoles::ROLE_ADMIN->value);
        $actions->setPermission(Action::NEW, UserRoles::ROLE_SUPER_ADMIN->value);
        $actions->setPermission(Action::EDIT, UserRoles::ROLE_SUPER_ADMIN->value);
        $actions->setPermission(Action::DELETE, UserRoles::ROLE_SUPER_ADMIN->value);
        $actions->setPermission(Action::DETAIL, UserRoles::ROLE_ADMIN->value);
        $actions->setPermission(Action::BATCH_DELETE, UserRoles::ROLE_SUPER_ADMIN->value);

        $actions->addBatchAction(
            Action::new('reimport', new TranslatableMessage('admin.feed.reimport.action'))
                ->linkToCrudAction('reimportBatch')
                ->setIcon('fa fa-rotate-right')
        );
        $actions->setPermission('reimport', UserRoles::ROLE_SUPER_ADMIN->value);

        return $actions;
    }

    /**
     * Force a re-import of the selected feeds, mirroring `app:feed:import --force`.
     *
     * Dispatches an async ReadFeedMessage per (enabled) feed via the same path the
     * scheduler uses; disabled feeds are skipped.
     */
    public function reimportBatch(BatchActionDto $batchActionDto): Response
    {
        $feedIds = array_map(intval(...), $batchActionDto->getEntityIds());

        $queued = iterator_to_array(
            $this->feedReader->readFeedsASync(FeedReaderInterface::DEFAULT_OPTION, true, $feedIds)
        );

        $this->addFlash('success', new TranslatableMessage('admin.feed.reimport.queued', ['count' => count($queued)]));

        $url = $this->container->get(AdminUrlGenerator::class)
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl();

        return $this->redirect($url);
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->setLabel(new TranslatableMessage('admin.feed.id'))
                ->setDisabled()
                ->hideWhenCreating(),

            TextField::new('name')
                ->setLabel(new TranslatableMessage('admin.feed.name')),
            AssociationField::new('organization')
                ->setLabel(new TranslatableMessage('admin.feed.organization'))
                ->hideOnIndex(),
            CodeEditorField::new('configurationField')
                ->setLabel(new TranslatableMessage('admin.feed.configuration'))
                ->setHelp(new TranslatableMessage('admin.feed.configuration.help'))
                ->setLanguage('js')
                ->hideOnIndex()
                ->setFormTypeOptions(
                    ['constraints' => [new Json(['message' => 'admin.feed.configuration.json_invalid'])]]
                ),

            // EasyAdmin does not disable the toggles even though the user can't edit
            BooleanField::new('enabled')->setDisabled(!$this->isGranted(UserRoles::ROLE_SUPER_ADMIN->value)),
            BooleanField::new('syncToFeed')->setDisabled(!$this->isGranted(UserRoles::ROLE_SUPER_ADMIN->value)),
            BooleanField::new('convertNewlinesToBr')->setDisabled(!$this->isGranted(UserRoles::ROLE_SUPER_ADMIN->value)),

            FormField::addFieldset(new TranslatableMessage('admin.feed.last_read.headline'))
                ->hideWhenCreating(),
            DateTimeField::new('lastRead')
                ->setLabel(new TranslatableMessage('admin.feed.last_read.datetime'))
                ->setDisabled()
                ->hideWhenCreating()
                ->setFormat(DashboardController::DATETIME_FORMAT),
            NumberField::new('lastReadCount')
                ->setLabel(new TranslatableMessage('admin.feed.last_read.count'))
                ->setDisabled()
                ->hideWhenCreating(),
            TextField::new('message')
                ->setLabel(new TranslatableMessage('admin.feed.last_read.error'))
                ->setDisabled()
                ->hideWhenCreating(),

            FormField::addFieldset(new TranslatableMessage('admin.feed.edited.headline'))
                ->hideWhenCreating(),
            DateTimeField::new('updated_at')
                ->setLabel(new TranslatableMessage('admin.feed.edited.update'))
                ->setDisabled()
                ->hideWhenCreating()
                ->hideOnIndex()
                ->setFormat(DashboardController::DATETIME_FORMAT),
        ];
    }
}
