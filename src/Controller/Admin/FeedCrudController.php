<?php

namespace App\Controller\Admin;

use App\Entity\Feed;
use App\Types\UserRoles;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\Json;

class FeedCrudController extends AbstractBaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return Feed::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);

        $actions->setPermission(Action::INDEX, UserRoles::ROLE_ADMIN->value);
        $actions->setPermission(Action::NEW, UserRoles::ROLE_SUPER_ADMIN->value);
        $actions->setPermission(Action::EDIT, UserRoles::ROLE_SUPER_ADMIN->value);
        $actions->setPermission(Action::DELETE, UserRoles::ROLE_SUPER_ADMIN->value);
        $actions->setPermission(Action::DETAIL, UserRoles::ROLE_ADMIN->value);
        $actions->setPermission(Action::BATCH_DELETE, UserRoles::ROLE_SUPER_ADMIN->value);

        return $actions;
    }

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
