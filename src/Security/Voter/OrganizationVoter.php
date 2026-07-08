<?php

namespace App\Security\Voter;

use App\Entity\Organization;
use App\Entity\User;
use App\Types\UserRoles;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class OrganizationVoter extends Voter
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

        // EasyAdmin passes a null entity for INDEX/NEW but always sets entityFqcn,
        // so match on that to keep NEW enforced at the URL level.
        $fqcn = $subject['entityFqcn'] ?? $subject['entity']?->getFqcn();

        return Organization::class === $fqcn;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?\Symfony\Component\Security\Core\Authorization\Voter\Vote $vote = null): bool
    {
        $user = $token->getUser();
        assert($user instanceof User);

        $action = is_string($subject['action']) ? $subject['action'] : $subject['action']->getName();

        // Detail and Index actions are allowed for all users
        if (Action::DETAIL === $action || Action::INDEX === $action) {
            return true;
        }

        // New action is only allowed for editors (EasyAdmin provides no instance yet)
        if (Action::NEW === $action) {
            return $this->security->isGranted(UserRoles::ROLE_EDITOR->value);
        }

        // Remaining actions operate on a concrete entity instance
        $organization = $subject['entity']->getInstance();
        assert($organization instanceof Organization);

        // Delete is only allowed for editors, and only when the organization is
        // not referenced by any events, feeds or partner events — deleting a
        // referenced organization would otherwise fail with a database
        // foreign-key violation.
        if (Action::DELETE === $action) {
            if (!$this->security->isGranted(UserRoles::ROLE_EDITOR->value)) {
                return false;
            }

            return $organization->getEvents()->isEmpty()
                && $organization->getFeeds()->isEmpty()
                && $organization->getPartnerEvents()->isEmpty();
        }

        // Global Admin/Editor users can edit all organizations
        if ($this->security->isGranted(UserRoles::ROLE_EDITOR->value)) {
            return true;
        }

        // Organization admins can only edit their own organizations
        if ($this->security->isGranted(UserRoles::ROLE_ORGANIZATION_ADMIN->value)) {
            return $user->getOrganizations()->contains($organization);
        }

        return false;
    }
}
