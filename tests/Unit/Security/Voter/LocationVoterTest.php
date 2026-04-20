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

    public function testEditorCanDeleteLocationWithoutEvents(): void
    {
        $voter = new LocationVoter($this->createSecurity([UserRoles::ROLE_EDITOR->value]));
        $subject = ['entity' => $this->createEntityDto(Location::class, new Location()), 'action' => Action::DELETE];
        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken(new User()), $subject, [Permission::EA_EXECUTE_ACTION]),
        );
    }

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

    public function testEditorCanEditLocation(): void
    {
        $voter = new LocationVoter($this->createSecurity([UserRoles::ROLE_EDITOR->value]));
        $subject = ['entity' => $this->createEntityDto(Location::class, new Location()), 'action' => Action::EDIT];
        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken(new User()), $subject, [Permission::EA_EXECUTE_ACTION]),
        );
    }

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
