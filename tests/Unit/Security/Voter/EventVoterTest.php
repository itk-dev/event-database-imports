<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security\Voter;

use App\Entity\Event;
use App\Entity\Feed;
use App\Entity\Organization;
use App\Entity\User;
use App\Security\Voter\EventVoter;
use App\Types\UserRoles;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

#[CoversClass(EventVoter::class)]
final class EventVoterTest extends TestCase
{
    use VoterTestHelperTrait;

    /**
     * Abstains when the voted-on entity is not an Event.
     */
    public function testAbstainsForUnsupportedEntity(): void
    {
        $voter = new EventVoter($this->createStub(Security::class));
        $token = $this->createToken(new User());

        $subject = [
            'entity' => $this->createEntityDto(Organization::class, new Organization()),
            'action' => Action::EDIT,
        ];
        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $voter->vote($token, $subject, [Permission::EA_EXECUTE_ACTION]),
        );
    }

    /**
     * Abstains when the attribute is not the EasyAdmin execute-action permission.
     */
    public function testAbstainsForUnsupportedAttribute(): void
    {
        $voter = new EventVoter($this->createStub(Security::class));
        $token = $this->createToken(new User());

        $subject = [
            'entity' => $this->createEntityDto(Event::class, new Event()),
            'action' => Action::EDIT,
        ];
        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $voter->vote($token, $subject, ['ROLE_USER']),
        );
    }

    /**
     * Grants detail and index actions regardless of the user's role.
     */
    public function testDetailAndIndexAreAlwaysGranted(): void
    {
        $voter = new EventVoter($this->createSecurity([]));
        $token = $this->createToken(new User());
        $event = new Event();

        foreach ([Action::DETAIL, Action::INDEX] as $action) {
            $subject = ['entity' => $this->createEntityDto(Event::class, $event), 'action' => $action];
            $this->assertSame(
                VoterInterface::ACCESS_GRANTED,
                $voter->vote($token, $subject, [Permission::EA_EXECUTE_ACTION]),
                sprintf('Expected %s action to be granted', $action),
            );
        }
    }

    /**
     * Grants save actions to a user with the organization editor role.
     */
    public function testSaveActionsAllowedForOrganizationEditor(): void
    {
        $voter = new EventVoter($this->createSecurity([UserRoles::ROLE_ORGANIZATION_EDITOR->value]));
        $token = $this->createToken(new User());
        $event = new Event();

        foreach ([Action::SAVE_AND_ADD_ANOTHER, Action::SAVE_AND_CONTINUE, Action::SAVE_AND_RETURN] as $action) {
            $subject = ['entity' => $this->createEntityDto(Event::class, $event), 'action' => $action];
            $this->assertSame(
                VoterInterface::ACCESS_GRANTED,
                $voter->vote($token, $subject, [Permission::EA_EXECUTE_ACTION]),
            );
        }
    }

    /**
     * Denies edit action for an event that belongs to a feed, even for an editor.
     */
    public function testFeedEventsAreNeverEditable(): void
    {
        $voter = new EventVoter($this->createSecurity([UserRoles::ROLE_EDITOR->value]));
        $token = $this->createToken(new User());

        $event = new Event();
        $event->setFeed(new Feed());

        $subject = ['entity' => $this->createEntityDto(Event::class, $event), 'action' => Action::EDIT];
        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($token, $subject, [Permission::EA_EXECUTE_ACTION]),
        );
    }

    /**
     * Feed events must never be editable, including via SAVE_AND_* actions:
     * the feed guard runs before the save-action grant.
     */
    public function testFeedEventsCannotBeSavedByOrganizationEditor(): void
    {
        $voter = new EventVoter($this->createSecurity([UserRoles::ROLE_ORGANIZATION_EDITOR->value]));
        $token = $this->createToken(new User());

        $event = new Event();
        $event->setFeed(new Feed());

        foreach ([Action::SAVE_AND_ADD_ANOTHER, Action::SAVE_AND_CONTINUE, Action::SAVE_AND_RETURN] as $action) {
            $subject = ['entity' => $this->createEntityDto(Event::class, $event), 'action' => $action];
            $this->assertSame(
                VoterInterface::ACCESS_DENIED,
                $voter->vote($token, $subject, [Permission::EA_EXECUTE_ACTION]),
                sprintf('Expected feed event %s to be denied', $action),
            );
        }
    }

    /**
     * Grants edit action to an editor for an event that does not belong to a feed.
     */
    public function testEditorCanEditNonFeedEvents(): void
    {
        $voter = new EventVoter($this->createSecurity([UserRoles::ROLE_EDITOR->value]));
        $token = $this->createToken(new User());
        $event = new Event();

        $subject = ['entity' => $this->createEntityDto(Event::class, $event), 'action' => Action::EDIT];
        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($token, $subject, [Permission::EA_EXECUTE_ACTION]),
        );
    }

    /**
     * Grants edit action to an organization editor for an event owned by their organization.
     */
    public function testOrganizationEditorCanEditOwnOrgEvent(): void
    {
        $voter = new EventVoter($this->createSecurity([UserRoles::ROLE_ORGANIZATION_EDITOR->value]));

        $org = new Organization();
        $user = new User();
        $user->addOrganization($org);

        $event = new Event();
        $event->setOrganization($org);

        $subject = ['entity' => $this->createEntityDto(Event::class, $event), 'action' => Action::EDIT];
        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken($user), $subject, [Permission::EA_EXECUTE_ACTION]),
        );
    }

    /**
     * Denies edit action to an organization editor for an event owned by another organization.
     */
    public function testOrganizationEditorCannotEditOtherOrgEvent(): void
    {
        $voter = new EventVoter($this->createSecurity([UserRoles::ROLE_ORGANIZATION_EDITOR->value]));

        $userOrg = new Organization();
        $otherOrg = new Organization();
        $user = new User();
        $user->addOrganization($userOrg);

        $event = new Event();
        $event->setOrganization($otherOrg);

        $subject = ['entity' => $this->createEntityDto(Event::class, $event), 'action' => Action::EDIT];
        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken($user), $subject, [Permission::EA_EXECUTE_ACTION]),
        );
    }

    /**
     * Denies edit action to an organization editor for an event with no organization set.
     */
    public function testOrganizationEditorCannotEditEventWithoutOrganization(): void
    {
        $voter = new EventVoter($this->createSecurity([UserRoles::ROLE_ORGANIZATION_EDITOR->value]));
        $user = new User();
        $user->addOrganization(new Organization());

        $event = new Event();

        $subject = ['entity' => $this->createEntityDto(Event::class, $event), 'action' => Action::EDIT];
        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken($user), $subject, [Permission::EA_EXECUTE_ACTION]),
        );
    }

    /**
     * Denies edit action for a user with no roles.
     */
    public function testUserWithoutRoleCannotEdit(): void
    {
        $voter = new EventVoter($this->createSecurity([]));
        $token = $this->createToken(new User());
        $event = new Event();

        $subject = ['entity' => $this->createEntityDto(Event::class, $event), 'action' => Action::EDIT];
        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($token, $subject, [Permission::EA_EXECUTE_ACTION]),
        );
    }
}
