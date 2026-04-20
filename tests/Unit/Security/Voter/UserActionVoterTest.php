<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security\Voter;

use App\Entity\Event;
use App\Entity\User;
use App\Security\Voter\UserActionVoter;
use App\Types\UserRoles;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

#[CoversClass(UserActionVoter::class)]
final class UserActionVoterTest extends TestCase
{
    use VoterTestHelperTrait;

    public function testAbstainsForUnsupportedEntity(): void
    {
        $voter = new UserActionVoter($this->createStub(Security::class));
        $subject = [
            'entity' => $this->createEntityDto(Event::class, new Event()),
            'action' => Action::EDIT,
        ];
        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $voter->vote($this->createToken($this->makeUser(1)), $subject, [Permission::EA_EXECUTE_ACTION]),
        );
    }

    public function testAbstainsForUnsupportedAttribute(): void
    {
        $voter = new UserActionVoter($this->createStub(Security::class));
        $subject = [
            'entity' => $this->createEntityDto(User::class, $this->makeUser(1)),
            'action' => Action::EDIT,
        ];
        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $voter->vote($this->createToken($this->makeUser(1)), $subject, ['ROLE_USER']),
        );
    }

    /**
     * Save actions have no explicit handling in UserActionVoter — the voter falls
     * through to the ownership check (`$loggedInUser === $user`). A non-admin can
     * therefore only save their own record. Locking this behaviour in so any change
     * is intentional and reviewed.
     */
    public function testSaveActionFallsThroughToOwnershipCheck(): void
    {
        $voter = new UserActionVoter($this->createSecurity([UserRoles::ROLE_USER->value]));

        $self = $this->makeUser(10);
        $other = $this->makeUser(20);

        foreach ([Action::SAVE_AND_ADD_ANOTHER, Action::SAVE_AND_CONTINUE, Action::SAVE_AND_RETURN] as $action) {
            $ownSubject = ['entity' => $this->createEntityDto(User::class, $self), 'action' => $action];
            $this->assertSame(
                VoterInterface::ACCESS_GRANTED,
                $voter->vote($this->createToken($self), $ownSubject, [Permission::EA_EXECUTE_ACTION]),
                sprintf('Expected %s on own record to pass the ownership check', $action),
            );

            $otherSubject = ['entity' => $this->createEntityDto(User::class, $other), 'action' => $action];
            $this->assertSame(
                VoterInterface::ACCESS_DENIED,
                $voter->vote($this->createToken($self), $otherSubject, [Permission::EA_EXECUTE_ACTION]),
                sprintf('Expected %s on another user to fail the ownership check', $action),
            );
        }
    }

    public function testCannotDeleteSelf(): void
    {
        $voter = new UserActionVoter($this->createSecurity([UserRoles::ROLE_ADMIN->value]));

        $self = $this->makeUser(10);
        $subject = ['entity' => $this->createEntityDto(User::class, $self), 'action' => Action::DELETE];
        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken($self), $subject, [Permission::EA_EXECUTE_ACTION]),
        );
    }

    public function testAdminCanDeleteOtherUser(): void
    {
        $voter = new UserActionVoter($this->createSecurity([UserRoles::ROLE_ADMIN->value]));

        $admin = $this->makeUser(1);
        $other = $this->makeUser(2);
        $subject = ['entity' => $this->createEntityDto(User::class, $other), 'action' => Action::DELETE];
        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken($admin), $subject, [Permission::EA_EXECUTE_ACTION]),
        );
    }

    public function testNonAdminCannotCreateNewUser(): void
    {
        $voter = new UserActionVoter($this->createSecurity([UserRoles::ROLE_USER->value]));

        $self = $this->makeUser(10);
        $subject = ['entity' => $this->createEntityDto(User::class, $this->makeUser(20)), 'action' => Action::NEW];
        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken($self), $subject, [Permission::EA_EXECUTE_ACTION]),
        );
    }

    public function testNonAdminCanEditSelf(): void
    {
        $voter = new UserActionVoter($this->createSecurity([UserRoles::ROLE_USER->value]));

        $self = $this->makeUser(10);
        $subject = ['entity' => $this->createEntityDto(User::class, $self), 'action' => Action::EDIT];
        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken($self), $subject, [Permission::EA_EXECUTE_ACTION]),
        );
    }

    public function testNonAdminCannotEditOther(): void
    {
        $voter = new UserActionVoter($this->createSecurity([UserRoles::ROLE_USER->value]));

        $self = $this->makeUser(10);
        $other = $this->makeUser(20);
        $subject = ['entity' => $this->createEntityDto(User::class, $other), 'action' => Action::EDIT];
        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken($self), $subject, [Permission::EA_EXECUTE_ACTION]),
        );
    }
}
