<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security\Voter;

use App\Entity\Event;
use App\Entity\Tag;
use App\Entity\User;
use App\Security\Voter\TagVoter;
use App\Types\UserRoles;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

#[CoversClass(TagVoter::class)]
final class TagVoterTest extends TestCase
{
    use VoterTestHelperTrait;

    public function testAbstainsForUnsupportedEntity(): void
    {
        $voter = new TagVoter($this->createStub(Security::class));
        $subject = [
            'entity' => $this->createEntityDto(Event::class, new Event()),
            'action' => Action::EDIT,
        ];
        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $voter->vote($this->createToken(new User()), $subject, [Permission::EA_EXECUTE_ACTION]),
        );
    }

    public function testAbstainsForUnsupportedAttribute(): void
    {
        $voter = new TagVoter($this->createStub(Security::class));
        $subject = [
            'entity' => $this->createEntityDto(Tag::class, new Tag()),
            'action' => Action::EDIT,
        ];
        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $voter->vote($this->createToken(new User()), $subject, ['ROLE_USER']),
        );
    }

    public function testAdminCanEditAndDeleteTag(): void
    {
        $voter = new TagVoter($this->createSecurity([UserRoles::ROLE_ADMIN->value]));

        foreach ([Action::EDIT, Action::DELETE] as $action) {
            $subject = ['entity' => $this->createEntityDto(Tag::class, new Tag()), 'action' => $action];
            $this->assertSame(
                VoterInterface::ACCESS_GRANTED,
                $voter->vote($this->createToken(new User()), $subject, [Permission::EA_EXECUTE_ACTION]),
            );
        }
    }

    public function testNonAdminCannotEditOrDeleteTag(): void
    {
        $voter = new TagVoter($this->createSecurity([UserRoles::ROLE_EDITOR->value]));

        foreach ([Action::EDIT, Action::DELETE] as $action) {
            $subject = ['entity' => $this->createEntityDto(Tag::class, new Tag()), 'action' => $action];
            $this->assertSame(
                VoterInterface::ACCESS_DENIED,
                $voter->vote($this->createToken(new User()), $subject, [Permission::EA_EXECUTE_ACTION]),
            );
        }
    }

    public function testOtherActionsAreAllowed(): void
    {
        $voter = new TagVoter($this->createSecurity([]));

        foreach ([Action::INDEX, Action::DETAIL, Action::NEW] as $action) {
            $subject = ['entity' => $this->createEntityDto(Tag::class, new Tag()), 'action' => $action];
            $this->assertSame(
                VoterInterface::ACCESS_GRANTED,
                $voter->vote($this->createToken(new User()), $subject, [Permission::EA_EXECUTE_ACTION]),
            );
        }
    }
}
