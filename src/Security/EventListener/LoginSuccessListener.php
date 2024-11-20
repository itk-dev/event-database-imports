<?php

namespace App\Security\EventListener;

use App\Entity\User;
use App\Types\UserRoles;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

final class LoginSuccessListener
{
    public function __construct(
        private readonly UrlGeneratorInterface $router,
        private readonly Security $security,
    ) {
    }

    #[AsEventListener(event: LoginSuccessEvent::class)]
    public function onLoginSuccessEvent(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        assert($user instanceof User);

        if (null === $user->getTermsAcceptedAt()) {
            $response = new RedirectResponse($this->router->generate('app_accept_terms'));
            $event->setResponse($response);
        }

        // Ensure email verified for non org. editor/admin users
        // Self registered users must have verified email
        // If a role (ROLE_ORGANIZATION_EDITOR or above) has been granted we assume email is valid
        // @TODO Add "resend confirmation" email page to enable email verification for all users
        if (null === $user->getEmailVerifiedAt() && !$this->security->isGranted(UserRoles::ROLE_ORGANIZATION_EDITOR->value)) {
            $session = $event->getRequest()->getSession();
            assert($session instanceof FlashBagAwareSessionInterface);

            $session->getFlashBag()->add('danger', 'registration.page.email_not_verified');
            // @TODO Add "resend confirmation" email page
            // $response = new RedirectResponse($this->router->generate('app_email_not_verified'));
            $response = new RedirectResponse($this->router->generate('app_admin_login'));
            $event->setResponse($response);
        }
    }
}
