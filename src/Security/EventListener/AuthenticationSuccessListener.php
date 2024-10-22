<?php

namespace App\Security\EventListener;

use App\Entity\User;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;

final class AuthenticationSuccessListener
{
    #[AsEventListener(event: AuthenticationSuccessEvent::class)]
    public function onSecurityAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();
        assert($user instanceof User);

        if (false === $user->isEnabled()) {
            throw new AccessDeniedHttpException();
        }
    }
}
