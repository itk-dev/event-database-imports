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

    /**
     * Abstains when the subject entity is not a User.
     */
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

    /**
     * Abstains when the security attribute is not the expected EasyAdmin action.
     */
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
     * A non-admin may save their own record but not another user's.
     */
    public function testNonAdminCanSaveOnlyOwnRecord(): void
    {
        $voter = new UserActionVoter($this->createSecurity([UserRoles::ROLE_USER->value]));

        $self = $this->makeUser(10);
        $other = $this->makeUser(20);

        foreach ([Action::SAVE_AND_ADD_ANOTHER, Action::SAVE_AND_CONTINUE, Action::SAVE_AND_RETURN] as $action) {
            $ownSubject = ['entity' => $this->createEntityDto(User::class, $self), 'action' => $action];
            $this->assertSame(
                VoterInterface::ACCESS_GRANTED,
                $voter->vote($this->createToken($self), $ownSubject, [Permission::EA_EXECUTE_ACTION]),
                sprintf('Expected %s on own record to be granted', $action),
            );

            $otherSubject = ['entity' => $this->createEntityDto(User::class, $other), 'action' => $action];
            $this->assertSame(
                VoterInterface::ACCESS_DENIED,
                $voter->vote($this->createToken($self), $otherSubject, [Permission::EA_EXECUTE_ACTION]),
                sprintf('Expected %s on another user to be denied', $action),
            );
        }
    }

    /**
     * An admin may save any user's record.
     */
    public function testAdminCanSaveAnotherUser(): void
    {
        $voter = new UserActionVoter($this->createSecurity([UserRoles::ROLE_ADMIN->value]));

        $admin = $this->makeUser(1);
        $other = $this->makeUser(2);

        foreach ([Action::SAVE_AND_ADD_ANOTHER, Action::SAVE_AND_CONTINUE, Action::SAVE_AND_RETURN] as $action) {
            $subject = ['entity' => $this->createEntityDto(User::class, $other), 'action' => $action];
            $this->assertSame(
                VoterInterface::ACCESS_GRANTED,
                $voter->vote($this->createToken($admin), $subject, [Permission::EA_EXECUTE_ACTION]),
                sprintf('Expected admin %s on another user to be granted', $action),
            );
        }
    }

    /**
     * Denies deleting one's own user account, even as an admin.
     */
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

    /**
     * Grants an admin permission to delete another user's account.
     */
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

    /**
     * Denies non-admin users permission to create a new user.
     */
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

    /**
     * EasyAdmin invokes the voter for INDEX/NEW with a null entity and only
     * the FQCN set. The voter must resolve the action without dereferencing the
     * (absent) instance and gate the user list to admins.
     */
    public function testNullEntityIndexAndNewAreAdminOnly(): void
    {
        $adminVoter = new UserActionVoter($this->createSecurity([UserRoles::ROLE_ADMIN->value]));
        $userVoter = new UserActionVoter($this->createSecurity([UserRoles::ROLE_USER->value]));
        $token = $this->createToken($this->makeUser(10));

        foreach ([Action::INDEX, Action::NEW] as $action) {
            $subject = ['entity' => null, 'entityFqcn' => User::class, 'action' => $action];

            $this->assertSame(
                VoterInterface::ACCESS_GRANTED,
                $adminVoter->vote($token, $subject, [Permission::EA_EXECUTE_ACTION]),
                sprintf('Expected admin to be granted %s', $action),
            );
            $this->assertSame(
                VoterInterface::ACCESS_DENIED,
                $userVoter->vote($token, $subject, [Permission::EA_EXECUTE_ACTION]),
                sprintf('Expected non-admin to be denied %s', $action),
            );
        }
    }

    /**
     * Grants a non-admin user permission to edit their own account.
     */
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

    /**
     * Denies a non-admin user permission to edit another user's account.
     */
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
