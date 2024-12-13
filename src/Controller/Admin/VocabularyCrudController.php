<?php

namespace App\Controller\Admin;

use App\Entity\Vocabulary;
use App\Types\UserRoles;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Translation\TranslatableMessage;

class VocabularyCrudController extends AbstractBaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return Vocabulary::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['name' => Order::Ascending->value])
            ->setSearchFields(['name'])
            ->showEntityActionsInlined()
            ->setPageTitle('edit', new TranslatableMessage('admin.vocabulary.edit.title'))
            ->setPageTitle('index', new TranslatableMessage('admin.vocabulary.index.title'))
            ->setPageTitle('detail', new TranslatableMessage('admin.vocabulary.edit.title'));
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);

        $actions->setPermission(Action::INDEX, UserRoles::ROLE_ADMIN->value);
        $actions->setPermission(Action::NEW, UserRoles::ROLE_ADMIN->value);
        $actions->setPermission(Action::EDIT, UserRoles::ROLE_ADMIN->value);
        $actions->setPermission(Action::DELETE, UserRoles::ROLE_ADMIN->value);
        $actions->setPermission(Action::DETAIL, UserRoles::ROLE_ADMIN->value);
        $actions->setPermission(Action::BATCH_DELETE, UserRoles::ROLE_ADMIN->value);

        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name')
                ->setLabel(new TranslatableMessage('admin.vocabulary.name')),
            AssociationField::new('tags')
                ->setQueryBuilder(
                    fn (QueryBuilder $queryBuilder) => $queryBuilder->addCriteria(
                        Criteria::create()->orderBy(['name' => Order::Ascending])
                    )
                )
                ->setLabel(new TranslatableMessage('admin.vocabulary.tags')),

            FormField::addFieldset(new TranslatableMessage('admin.vocabulary.edited.headline'))
                ->hideWhenCreating(),
            DateTimeField::new('updated_at')
                ->setLabel(new TranslatableMessage('admin.vocabulary.edited.updated'))
                ->setDisabled()
                ->hideWhenCreating()
                ->setFormat(DashboardController::DATETIME_FORMAT),
        ];
    }
}
