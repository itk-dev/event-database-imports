<?php

namespace App\Security\Voter;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class UserEntityVoter extends Voter
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return Permission::EA_ACCESS_ENTITY == $attribute
            && $subject instanceof EntityDto
            && User::class === $subject->getFqcn();
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof User && User::class === $subject->getFqcn()) {
            return false;
        }

        assert($subject instanceof EntityDto);
        assert($user instanceof User);

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return $subject->getPrimaryKeyValue() === $user->getId();
    }
}
