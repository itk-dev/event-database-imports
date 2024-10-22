<?php

namespace App\Security\Voter;

use App\Entity\Address;
use App\Entity\Tag;
use App\Entity\User;
use App\Types\UserRoles;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class TagVoter extends Voter
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return Permission::EA_EXECUTE_ACTION == $attribute
            && null !== $subject['entity']
            && Tag::class === $subject['entity']->getFqcn();
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        assert($user instanceof User);

        $tag = $subject['entity']->getInstance();
        assert($tag instanceof Tag);

        $action = is_string($subject['action']) ? $subject['action'] : $subject['action']->getName();

        // Delete actions are only allowed for admins
        if (Action::DELETE === $action || Action::EDIT === $action) {
            if (!$this->security->isGranted(UserRoles::ROLE_ADMIN->value)) {
                return false;
            }
        }

        return true;
    }
}
