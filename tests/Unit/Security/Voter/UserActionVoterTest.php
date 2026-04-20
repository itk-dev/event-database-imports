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

    private function makeUser(int $id): User
    {
        $user = new User();
        $reflection = new \ReflectionProperty(User::class, 'id');
        $reflection->setValue($user, $id);

        return $user;
    }
}
