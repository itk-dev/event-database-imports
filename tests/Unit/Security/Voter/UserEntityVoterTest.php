<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security\Voter;

use App\Entity\Event;
use App\Entity\User;
use App\Security\Voter\UserEntityVoter;
use App\Types\UserRoles;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

#[CoversClass(UserEntityVoter::class)]
final class UserEntityVoterTest extends TestCase
{
    use VoterTestHelperTrait;

    public function testAbstainsForUnsupportedEntity(): void
    {
        $voter = new UserEntityVoter($this->createStub(Security::class));
        $dto = $this->createEntityDto(Event::class, null);
        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $voter->vote($this->createToken($this->makeUser(1)), $dto, [Permission::EA_ACCESS_ENTITY]),
        );
    }

    public function testAbstainsForUnsupportedAttribute(): void
    {
        $voter = new UserEntityVoter($this->createStub(Security::class));
        $dto = $this->createEntityDto(User::class, $this->makeUser(1));
        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $voter->vote($this->createToken($this->makeUser(1)), $dto, ['ROLE_USER']),
        );
    }

    public function testAnonymousUserIsDenied(): void
    {
        $voter = new UserEntityVoter($this->createStub(Security::class));
        $token = $this->createStub(TokenInterface::class);
        $token->method('getUser')->willReturn(null);

        $dto = $this->createEntityDto(User::class, $this->makeUser(1));
        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($token, $dto, [Permission::EA_ACCESS_ENTITY]),
        );
    }

    public function testAdminCanAccessAnyUser(): void
    {
        $voter = new UserEntityVoter($this->createSecurity([UserRoles::ROLE_ADMIN->value]));
        $self = $this->makeUser(1);
        $other = $this->makeUser(2);

        $dto = $this->createEntityDto(User::class, $other);
        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken($self), $dto, [Permission::EA_ACCESS_ENTITY]),
        );
    }

    public function testNonAdminCanAccessOwnUser(): void
    {
        $voter = new UserEntityVoter($this->createSecurity([]));
        $self = $this->makeUser(5);

        $dto = $this->createEntityDto(User::class, $self);
        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken($self), $dto, [Permission::EA_ACCESS_ENTITY]),
        );
    }

    public function testNonAdminCannotAccessOtherUser(): void
    {
        $voter = new UserEntityVoter($this->createSecurity([]));
        $self = $this->makeUser(5);
        $other = $this->makeUser(10);

        $dto = $this->createEntityDto(User::class, $other);
        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken($self), $dto, [Permission::EA_ACCESS_ENTITY]),
        );
    }
}
