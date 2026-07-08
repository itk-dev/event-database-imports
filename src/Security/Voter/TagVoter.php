<?php

namespace App\Security\Voter;

use App\Entity\Tag;
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
        if (Permission::EA_EXECUTE_ACTION !== $attribute) {
            return false;
        }

        // EasyAdmin passes a null entity for INDEX/NEW but always sets entityFqcn.
        $fqcn = $subject['entityFqcn'] ?? $subject['entity']?->getFqcn();

        return Tag::class === $fqcn;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?\Symfony\Component\Security\Core\Authorization\Voter\Vote $vote = null): bool
    {
        $action = is_string($subject['action']) ? $subject['action'] : $subject['action']->getName();

        // Delete and edit are only allowed for admins.
        if (Action::DELETE === $action || Action::EDIT === $action) {
            return $this->security->isGranted(UserRoles::ROLE_ADMIN->value);
        }

        // Index, detail, new and save are open to any authenticated user
        // (assigning a tag to a vocabulary is gated separately on the form field).
        return true;
    }
}
