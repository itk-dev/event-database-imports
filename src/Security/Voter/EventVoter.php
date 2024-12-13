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
        return Permission::EA_EXECUTE_ACTION == $attribute
            && null !== $subject['entity']
            && Event::class === $subject['entity']->getFqcn();
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        assert($user instanceof User);

        $event = $subject['entity']->getInstance();
        assert($event instanceof Event);

        $action = is_string($subject['action']) ? $subject['action'] : $subject['action']->getName();

        // Detail and Index actions are allowed for all users
        if (Action::DETAIL === $action || Action::INDEX === $action) {
            return true;
        }

        if (Action::SAVE_AND_ADD_ANOTHER === $action || Action::SAVE_AND_CONTINUE === $action || Action::SAVE_AND_RETURN === $action) {
            // Allow event creation
            if ($this->security->isGranted(UserRoles::ROLE_ORGANIZATION_EDITOR->value)) {
                return true;
            }
        }

        // Feed events can never be edited or deleted
        if (null !== $event->getFeed()) {
            return false;
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
            } else {
                return $user->getOrganizations()->contains($organization);
            }
        }

        return false;
    }
}
