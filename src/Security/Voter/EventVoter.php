<?php

namespace App\Security\Voter;

use App\Entity\Event;
use App\Entity\User;
use App\Types\UserRoles;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class EventVoter extends Voter
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

        return Event::class === $fqcn;
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

        // New action requires the organization editor role (no entity instance yet)
        if (Action::NEW === $action) {
            return $this->security->isGranted(UserRoles::ROLE_ORGANIZATION_EDITOR->value);
        }

        // Remaining actions operate on a concrete event instance
        $event = $subject['entity']->getInstance();
        assert($event instanceof Event);

        // Feed events can never be edited or deleted. Apply this before any
        // save-action grant below so it cannot be bypassed via SAVE_AND_*.
        if (null !== $event->getFeed()) {
            return false;
        }

        if (Action::SAVE_AND_ADD_ANOTHER === $action || Action::SAVE_AND_CONTINUE === $action || Action::SAVE_AND_RETURN === $action) {
            // Global editors may save any event.
            if ($this->security->isGranted(UserRoles::ROLE_EDITOR->value)) {
                return true;
            }

            // Organization editors may only save events for their own organization(s),
            // mirroring the edit-path scoping below.
            if ($this->security->isGranted(UserRoles::ROLE_ORGANIZATION_EDITOR->value)) {
                $organization = $event->getOrganization();

                return null !== $organization && $user->getOrganizations()->contains($organization);
            }
        }

        // Global Admin/Editor users can edit all events except feed events
        if ($this->security->isGranted(UserRoles::ROLE_EDITOR->value)) {
            return true;
        }

        // Organization users can only edit non-feed events from their organizations
        if ($this->security->isGranted(UserRoles::ROLE_ORGANIZATION_EDITOR->value)) {
            $organization = $event->getOrganization();
            if (null === $organization) {
                return false;
            }

            return $user->getOrganizations()->contains($organization);
        }

        return false;
    }
}
