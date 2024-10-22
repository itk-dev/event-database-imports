<?php

namespace App\Security\Voter;

use App\Entity\Tag;
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
        return Permission::EA_EXECUTE_ACTION == $attribute
            && null !== $subject['entity']
            && User::class === $subject['entity']->getFqcn();
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $loggedInUser = $token->getUser();
        assert($loggedInUser instanceof User);

        $user = $subject['entity']->getInstance();
        assert($user instanceof Tag);

        $action = is_string($subject['action']) ? $subject['action'] : $subject['action']->getName();

        // You cannot delete your own account
        if (Action::DELETE === $action && $loggedInUser->getId() === $user->getId()) {
            return false;
        }

        // Admin can CRUD users
        if ($this->security->isGranted(UserRoles::ROLE_ADMIN->value)) {
            return true;
        }

        // Non-admin users cannot create new users
        if ($this->security->isGranted(UserRoles::ROLE_USER->value)) {
            if (Action::NEW === $action) {
                return false;
            }
        }

        // Non-admin users can edit their own account
        return $loggedInUser->getId() === $user->getId();
    }
}
