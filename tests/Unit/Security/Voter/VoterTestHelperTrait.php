<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security\Voter;

use App\Entity\User;
use Doctrine\ORM\Mapping\ClassMetadata;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

trait VoterTestHelperTrait
{
    /**
     * @param list<string> $grantedRoles
     */
    private function createSecurity(array $grantedRoles): Security
    {
        $security = $this->createStub(Security::class);
        $security->method('isGranted')->willReturnCallback(
            fn (mixed $attribute): bool => \in_array($attribute, $grantedRoles, true),
        );

        return $security;
    }

    private function createToken(User $user): TokenInterface
    {
        $token = $this->createStub(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        return $token;
    }

    /**
     * @param class-string $fqcn
     */
    private function createEntityDto(string $fqcn, ?object $instance): EntityDto
    {
        $metadata = new ClassMetadata($fqcn);
        $metadata->identifier = ['id'];

        return new EntityDto($fqcn, $metadata, null, $instance);
    }
}
