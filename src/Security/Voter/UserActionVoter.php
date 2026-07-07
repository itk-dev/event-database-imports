<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Types\UserRoles;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class UserActionVoter extends Voter
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

        return User::class === $fqcn;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $loggedInUser = $token->getUser();
        assert($loggedInUser instanceof User);

        $action = is_string($subject['action']) ? $subject['action'] : $subject['action']->getName();

        // Creating users and listing all users are admin-only. EasyAdmin passes
        // a null entity for both NEW and INDEX, so handle them before touching
        // the (absent) instance.
        if (Action::NEW === $action || Action::INDEX === $action) {
            return $this->security->isGranted(UserRoles::ROLE_ADMIN->value);
        }

        // Remaining actions operate on a concrete user instance.
        $user = $subject['entity']->getInstance();
        assert($user instanceof User);

        // You cannot delete your own account.
        if (Action::DELETE === $action && $loggedInUser->getId() === $user->getId()) {
            return false;
        }

        // Admins may manage any user (create/edit/save/delete/detail).
        if ($this->security->isGranted(UserRoles::ROLE_ADMIN->value)) {
            return true;
        }

        // Non-admins (edit, save, detail) may only act on their own account.
        return $loggedInUser->getId() === $user->getId();
    }
}
