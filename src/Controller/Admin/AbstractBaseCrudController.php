<?php

namespace App\Controller\Admin;

use App\Entity\EditableEntityInterface;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractBaseCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return self::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    protected function getUser(): User
    {
        $user = parent::getUser();
        assert($user instanceof User);

        return $user;
    }
}
