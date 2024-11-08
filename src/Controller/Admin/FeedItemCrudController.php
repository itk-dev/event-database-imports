<?php

namespace App\Controller\Admin;

use App\Entity\FeedItem;
use App\Types\UserRoles;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Translation\TranslatableMessage;

class FeedItemCrudController extends AbstractBaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return FeedItem::class;
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
        $actions->setPermission(Action::DETAIL, UserRoles::ROLE_ADMIN->value);

        $actions->disable(Action::BATCH_DELETE, Action::DELETE, Action::EDIT, Action::NEW);

        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->setLabel(new TranslatableMessage('admin.feeditem.id'))
                ->setDisabled()
                ->hideWhenCreating(),
            AssociationField::new('feed')
                ->setLabel(new TranslatableMessage('admin.feeditem.feed')),
            TextField::new('feedItemId')
                ->setLabel(new TranslatableMessage('admin.feeditem.feeditemid')),
            AssociationField::new('event')
                ->setLabel(new TranslatableMessage('admin.feeditem.event')),
            TextField::new('message')
                ->setLabel(new TranslatableMessage('admin.feeditem.message')),
            DateTimeField::new('lastSeenAt')
                ->setLabel(new TranslatableMessage('admin.feeditem.last_read.datetime'))
                ->setDisabled()
                ->hideWhenCreating()
                ->setFormat(DashboardController::DATETIME_FORMAT),
            CodeEditorField::new('data')
                ->setLabel(new TranslatableMessage('admin.feeditem.data'))
                ->formatValue(function ($value) {
                    return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                })
                ->setLanguage('js')
                ->hideOnIndex(),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        if ($this->isGranted(UserRoles::ROLE_EDITOR->value)) {
            $filters->add('feed');
            $filters->add('data');
            $filters->add('message');
            $filters->add('lastSeenAt');
        }

        return $filters;
    }
}
