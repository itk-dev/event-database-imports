<?php

namespace App\Controller\Admin;

use App\Entity\EditableEntityInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

abstract class AbstractBaseCrudController extends AbstractCrudController
{
    public function __construct(
        protected readonly int $excerptMaxLength,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return self::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->update(Crud::PAGE_INDEX, Action::EDIT, static function (Action $action) {
                return $action->displayIf(static function (object $entity) {
                    return !($entity instanceof EditableEntityInterface) || $entity->isEditable();
                });
            })
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }
}
