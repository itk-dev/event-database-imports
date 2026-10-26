<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Exception\EntityRemoveException;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatableMessage;

abstract class AbstractBaseCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return self::class;
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    /**
     * Turn a foreign-key violation on delete into a friendly flash + redirect
     * instead of EasyAdmin's raw 409 error page. Voters already hide/deny delete
     * for the "in use" cases they know about (see the entity voters); this is the
     * backstop for referential dependencies not mirrored in a voter.
     */
    #[\Override]
    public function delete(AdminContext $context): KeyValueStore|Response
    {
        try {
            return parent::delete($context);
        } catch (EntityRemoveException) {
            $this->addFlash('danger', new TranslatableMessage('admin.crud.delete.in_use'));

            $urlGenerator = $this->container->get(AdminUrlGeneratorInterface::class);
            assert($urlGenerator instanceof AdminUrlGeneratorInterface);

            return $this->redirect(
                $urlGenerator
                    ->setController(static::class)
                    ->setAction(Action::INDEX)
                    ->unset(EA::ENTITY_ID)
                    ->generateUrl()
            );
        }
    }

    #[\Override]
    protected function getUser(): User
    {
        $user = parent::getUser();
        assert($user instanceof User);

        return $user;
    }
}
