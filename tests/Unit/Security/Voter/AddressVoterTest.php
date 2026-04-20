<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security\Voter;

use App\Entity\Address;
use App\Entity\Event;
use App\Entity\Location;
use App\Entity\User;
use App\Security\Voter\AddressVoter;
use App\Types\UserRoles;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

#[CoversClass(AddressVoter::class)]
final class AddressVoterTest extends TestCase
{
    use VoterTestHelperTrait;

    public function testAbstainsForUnsupportedEntity(): void
    {
        $voter = new AddressVoter($this->createStub(Security::class));
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
        $voter = new AddressVoter($this->createSecurity([]));
        foreach ([Action::DETAIL, Action::INDEX] as $action) {
            $subject = ['entity' => $this->createEntityDto(Address::class, new Address()), 'action' => $action];
            $this->assertSame(
                VoterInterface::ACCESS_GRANTED,
                $voter->vote($this->createToken(new User()), $subject, [Permission::EA_EXECUTE_ACTION]),
            );
        }
    }

    public function testOrganizationAdminCanCreateAddress(): void
    {
        $voter = new AddressVoter($this->createSecurity([UserRoles::ROLE_ORGANIZATION_ADMIN->value]));
        foreach ([Action::SAVE_AND_ADD_ANOTHER, Action::SAVE_AND_CONTINUE, Action::SAVE_AND_RETURN] as $action) {
            $subject = ['entity' => $this->createEntityDto(Address::class, new Address()), 'action' => $action];
            $this->assertSame(
                VoterInterface::ACCESS_GRANTED,
                $voter->vote($this->createToken(new User()), $subject, [Permission::EA_EXECUTE_ACTION]),
            );
        }
    }

    public function testEditorCanDeleteAddressWithoutLocations(): void
    {
        $voter = new AddressVoter($this->createSecurity([UserRoles::ROLE_EDITOR->value]));
        $subject = ['entity' => $this->createEntityDto(Address::class, new Address()), 'action' => Action::DELETE];
        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken(new User()), $subject, [Permission::EA_EXECUTE_ACTION]),
        );
    }

    public function testEditorCannotDeleteAddressWithLocations(): void
    {
        $voter = new AddressVoter($this->createSecurity([UserRoles::ROLE_EDITOR->value]));

        $address = new Address();
        $address->addLocation(new Location());

        $subject = ['entity' => $this->createEntityDto(Address::class, $address), 'action' => Action::DELETE];
        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken(new User()), $subject, [Permission::EA_EXECUTE_ACTION]),
        );
    }

    public function testNonEditorCannotDeleteOrCreate(): void
    {
        $voter = new AddressVoter($this->createSecurity([UserRoles::ROLE_ORGANIZATION_EDITOR->value]));

        foreach ([Action::NEW, Action::DELETE] as $action) {
            $subject = ['entity' => $this->createEntityDto(Address::class, new Address()), 'action' => $action];
            $this->assertSame(
                VoterInterface::ACCESS_DENIED,
                $voter->vote($this->createToken(new User()), $subject, [Permission::EA_EXECUTE_ACTION]),
            );
        }
    }

    public function testEditorCanEditAddress(): void
    {
        $voter = new AddressVoter($this->createSecurity([UserRoles::ROLE_EDITOR->value]));
        $subject = ['entity' => $this->createEntityDto(Address::class, new Address()), 'action' => Action::EDIT];
        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken(new User()), $subject, [Permission::EA_EXECUTE_ACTION]),
        );
    }

    public function testUserWithoutRoleCannotEdit(): void
    {
        $voter = new AddressVoter($this->createSecurity([]));
        $subject = ['entity' => $this->createEntityDto(Address::class, new Address()), 'action' => Action::EDIT];
        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken(new User()), $subject, [Permission::EA_EXECUTE_ACTION]),
        );
    }
}
