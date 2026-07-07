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
        if (Permission::EA_EXECUTE_ACTION != $attribute) {
            return false;
        }

        // EasyAdmin passes a null entity for INDEX/NEW but always sets entityFqcn,
        // so match on that to keep NEW enforced at the URL level.
        $fqcn = $subject['entityFqcn'] ?? $subject['entity']?->getFqcn();

        return Location::class === $fqcn;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        assert($user instanceof User);

        $action = is_string($subject['action']) ? $subject['action'] : $subject['action']->getName();

        // Detail and Index actions are allowed for all users
        if (Action::DETAIL === $action || Action::INDEX === $action) {
            return true;
        }

        // New action is only allowed for editors (no entity instance yet)
        if (Action::NEW === $action) {
            return $this->security->isGranted(UserRoles::ROLE_EDITOR->value);
        }

        // Remaining actions operate on a concrete location instance
        $location = $subject['entity']->getInstance();
        assert($location instanceof Location);

        if (Action::SAVE_AND_ADD_ANOTHER === $action || Action::SAVE_AND_CONTINUE === $action || Action::SAVE_AND_RETURN === $action) {
            // Allow location creation
            if ($this->security->isGranted(UserRoles::ROLE_ORGANIZATION_ADMIN->value)) {
                return true;
            }
        }

        // Delete is only allowed for editors, and only for unused locations
        if (Action::DELETE === $action) {
            if ($this->security->isGranted(UserRoles::ROLE_EDITOR->value)) {
                return 0 === $location->getEvents()->count();
            }

            return false;
        }

        // Global Admin/Editor users can edit all locations
        if ($this->security->isGranted(UserRoles::ROLE_EDITOR->value)) {
            return true;
        }

        return false;
    }
}
