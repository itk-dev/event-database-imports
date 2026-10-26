<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security\Voter;

use App\Entity\Event;
use App\Entity\Location;
use App\Entity\User;
use App\Security\Voter\LocationVoter;
use App\Types\UserRoles;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

#[CoversClass(LocationVoter::class)]
final class LocationVoterTest extends TestCase
{
    use VoterTestHelperTrait;

    /**
     * Abstains when the voted-on entity is not a Location.
     */
    public function testAbstainsForUnsupportedEntity(): void
    {
        $voter = new LocationVoter($this->createStub(Security::class));
        $subject = [
            'entity' => $this->createEntityDto(Event::class, new Event()),
            'action' => Action::EDIT,
        ];
        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $voter->vote($this->createToken(new User()), $subject, [Permission::EA_EXECUTE_ACTION]),
        );
    }

    /**
     * Abstains when the attribute is not the EasyAdmin execute-action permission.
     */
    public function testAbstainsForUnsupportedAttribute(): void
    {
        $voter = new LocationVoter($this->createStub(Security::class));
        $subject = [
            'entity' => $this->createEntityDto(Location::class, new Location()),
            'action' => Action::EDIT,
        ];
        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $voter->vote($this->createToken(new User()), $subject, ['ROLE_USER']),
        );
    }

    /**
     * Denies save actions for an organization editor lacking the organization admin role.
     */
    public function testOrganizationEditorCannotSaveWithoutOrgAdmin(): void
    {
        $voter = new LocationVoter($this->createSecurity([UserRoles::ROLE_ORGANIZATION_EDITOR->value]));
        foreach ([Action::SAVE_AND_ADD_ANOTHER, Action::SAVE_AND_CONTINUE, Action::SAVE_AND_RETURN] as $action) {
            $subject = ['entity' => $this->createEntityDto(Location::class, new Location()), 'action' => $action];
            $this->assertSame(
                VoterInterface::ACCESS_DENIED,
                $voter->vote($this->createToken(new User()), $subject, [Permission::EA_EXECUTE_ACTION]),
                sprintf('Expected %s to be denied without ROLE_ORGANIZATION_ADMIN', $action),
            );
        }
    }

    /**
     * Grants detail and index actions regardless of the user's role.
     */
    public function testDetailAndIndexAreAlwaysGranted(): void
    {
        $voter = new LocationVoter($this->createSecurity([]));
        foreach ([Action::DETAIL, Action::INDEX] as $action) {
            $subject = ['entity' => $this->createEntityDto(Location::class, new Location()), 'action' => $action];
            $this->assertSame(
                VoterInterface::ACCESS_GRANTED,
                $voter->vote($this->createToken(new User()), $subject, [Permission::EA_EXECUTE_ACTION]),
            );
        }
    }

    /**
     * Grants save actions to an organization admin, allowing location creation.
     */
    public function testOrganizationAdminCanCreateLocation(): void
    {
        $voter = new LocationVoter($this->createSecurity([UserRoles::ROLE_ORGANIZATION_ADMIN->value]));
        foreach ([Action::SAVE_AND_ADD_ANOTHER, Action::SAVE_AND_CONTINUE, Action::SAVE_AND_RETURN] as $action) {
            $subject = ['entity' => $this->createEntityDto(Location::class, new Location()), 'action' => $action];
            $this->assertSame(
                VoterInterface::ACCESS_GRANTED,
                $voter->vote($this->createToken(new User()), $subject, [Permission::EA_EXECUTE_ACTION]),
            );
        }
    }

    /**
     * Grants delete for an editor when the location has no associated events.
     */
    public function testEditorCanDeleteLocationWithoutEvents(): void
    {
        $voter = new LocationVoter($this->createSecurity([UserRoles::ROLE_EDITOR->value]));
        $subject = ['entity' => $this->createEntityDto(Location::class, new Location()), 'action' => Action::DELETE];
        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken(new User()), $subject, [Permission::EA_EXECUTE_ACTION]),
        );
    }

    /**
     * Denies delete for an editor when the location still has events attached.
     */
    public function testEditorCannotDeleteLocationWithEvents(): void
    {
        $voter = new LocationVoter($this->createSecurity([UserRoles::ROLE_EDITOR->value]));

        $location = new Location();
        $event = new Event();
        $location->addEvent($event);

        $subject = ['entity' => $this->createEntityDto(Location::class, $location), 'action' => Action::DELETE];
        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken(new User()), $subject, [Permission::EA_EXECUTE_ACTION]),
        );
    }

    /**
     * Denies new and delete actions for a role below editor.
     */
    public function testNonEditorCannotDeleteOrCreate(): void
    {
        $voter = new LocationVoter($this->createSecurity([UserRoles::ROLE_ORGANIZATION_EDITOR->value]));

        foreach ([Action::NEW, Action::DELETE] as $action) {
            $subject = ['entity' => $this->createEntityDto(Location::class, new Location()), 'action' => $action];
            $this->assertSame(
                VoterInterface::ACCESS_DENIED,
                $voter->vote($this->createToken(new User()), $subject, [Permission::EA_EXECUTE_ACTION]),
            );
        }
    }

    /**
     * Grants edit action to a user with the editor role.
     */
    public function testEditorCanEditLocation(): void
    {
        $voter = new LocationVoter($this->createSecurity([UserRoles::ROLE_EDITOR->value]));
        $subject = ['entity' => $this->createEntityDto(Location::class, new Location()), 'action' => Action::EDIT];
        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken(new User()), $subject, [Permission::EA_EXECUTE_ACTION]),
        );
    }

    /**
     * Denies edit action for a user with no roles.
     */
    public function testUserWithoutRoleCannotEdit(): void
    {
        $voter = new LocationVoter($this->createSecurity([]));
        $subject = ['entity' => $this->createEntityDto(Location::class, new Location()), 'action' => Action::EDIT];
        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken(new User()), $subject, [Permission::EA_EXECUTE_ACTION]),
        );
    }
}
