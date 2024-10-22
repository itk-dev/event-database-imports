<?php

namespace App\Security\Voter;

use App\Entity\Location;
use App\Entity\User;
use App\Types\UserRoles;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class LocationVoter extends Voter
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return Permission::EA_EXECUTE_ACTION == $attribute
            && null !== $subject['entity']
            && Location::class === $subject['entity']->getFqcn();
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        assert($user instanceof User);

        $location = $subject['entity']->getInstance();
        assert($location instanceof Location);

        $action = is_string($subject['action']) ? $subject['action'] : $subject['action']->getName();

        // Detail and Index actions are allowed for all users
        if (Action::DETAIL === $action || Action::INDEX === $action) {
            return true;
        }

        if (Action::SAVE_AND_ADD_ANOTHER === $action || Action::SAVE_AND_CONTINUE === $action || Action::SAVE_AND_RETURN === $action) {
            // Allow location creation
            if ($this->security->isGranted(UserRoles::ROLE_ORGANIZATION_ADMIN->value)) {
                return true;
            }
        }

        // Delete and New actions are only allowed for editors
        if (Action::DELETE === $action || Action::NEW === $action) {
            if ($this->security->isGranted(UserRoles::ROLE_EDITOR->value)) {
                return 0 === $location->getEvents()->count();
            } else {
                return false;
            }
        }

        // Global Admin/Editor users can edit all locations
        if ($this->security->isGranted(UserRoles::ROLE_EDITOR->value)) {
            return true;
        }

        return false;
    }
}
