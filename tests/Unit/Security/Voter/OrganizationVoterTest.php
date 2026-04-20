<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security\Voter;

use App\Entity\Event;
use App\Entity\Organization;
use App\Entity\User;
use App\Security\Voter\OrganizationVoter;
use App\Types\UserRoles;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

#[CoversClass(OrganizationVoter::class)]
final class OrganizationVoterTest extends TestCase
{
    use VoterTestHelperTrait;

    public function testAbstainsForUnsupportedEntity(): void
    {
        $voter = new OrganizationVoter($this->createStub(Security::class));
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
        $voter = new OrganizationVoter($this->createSecurity([]));
        foreach ([Action::DETAIL, Action::INDEX] as $action) {
            $subject = ['entity' => $this->createEntityDto(Organization::class, new Organization()), 'action' => $action];
            $this->assertSame(
                VoterInterface::ACCESS_GRANTED,
                $voter->vote($this->createToken(new User()), $subject, [Permission::EA_EXECUTE_ACTION]),
            );
        }
    }

    public function testNewAndDeleteRequireEditor(): void
    {
        foreach ([Action::NEW, Action::DELETE] as $action) {
            $editorVoter = new OrganizationVoter($this->createSecurity([UserRoles::ROLE_EDITOR->value]));
            $subject = ['entity' => $this->createEntityDto(Organization::class, new Organization()), 'action' => $action];
            $this->assertSame(
                VoterInterface::ACCESS_GRANTED,
                $editorVoter->vote($this->createToken(new User()), $subject, [Permission::EA_EXECUTE_ACTION]),
            );

            $orgAdminVoter = new OrganizationVoter($this->createSecurity([UserRoles::ROLE_ORGANIZATION_ADMIN->value]));
            $this->assertSame(
                VoterInterface::ACCESS_DENIED,
                $orgAdminVoter->vote($this->createToken(new User()), $subject, [Permission::EA_EXECUTE_ACTION]),
            );
        }
    }

    public function testEditorCanEditAnyOrganization(): void
    {
        $voter = new OrganizationVoter($this->createSecurity([UserRoles::ROLE_EDITOR->value]));
        $subject = ['entity' => $this->createEntityDto(Organization::class, new Organization()), 'action' => Action::EDIT];
        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken(new User()), $subject, [Permission::EA_EXECUTE_ACTION]),
        );
    }

    public function testOrganizationAdminCanEditOwnOrganization(): void
    {
        $voter = new OrganizationVoter($this->createSecurity([UserRoles::ROLE_ORGANIZATION_ADMIN->value]));

        $org = new Organization();
        $user = new User();
        $user->addOrganization($org);

        $subject = ['entity' => $this->createEntityDto(Organization::class, $org), 'action' => Action::EDIT];
        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($this->createToken($user), $subject, [Permission::EA_EXECUTE_ACTION]),
        );
    }

    public function testOrganizationAdminCannotEditOtherOrganization(): void
    {
        $voter = new OrganizationVoter($this->createSecurity([UserRoles::ROLE_ORGANIZATION_ADMIN->value]));

        $user = new User();
        $user->addOrganization(new Organization());
        $otherOrg = new Organization();

        $subject = ['entity' => $this->createEntityDto(Organization::class, $otherOrg), 'action' => Action::EDIT];
        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken($user), $subject, [Permission::EA_EXECUTE_ACTION]),
        );
    }

    public function testUserWithoutRoleCannotEdit(): void
    {
        $voter = new OrganizationVoter($this->createSecurity([]));
        $subject = ['entity' => $this->createEntityDto(Organization::class, new Organization()), 'action' => Action::EDIT];
        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($this->createToken(new User()), $subject, [Permission::EA_EXECUTE_ACTION]),
        );
    }
}
