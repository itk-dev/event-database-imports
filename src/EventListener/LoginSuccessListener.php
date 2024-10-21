<?php

namespace App\EventListener;

use App\Entity\User;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

final class LoginSuccessListener
{
    public function __construct(
        private readonly UrlGeneratorInterface $router,
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

        if (false === $user->isVerified()) {
            $session = $event->getRequest()->getSession();
            assert($session instanceof FlashBagAwareSessionInterface);

            $session->getFlashBag()->add('danger', 'login.page.email_not_verified');
            // @TODO Add "resend confirmation" email page
            //$response = new RedirectResponse($this->router->generate('app_email_not_verified'));
            $response = new RedirectResponse($this->router->generate('app_admin_login'));
            $event->setResponse($response);
        }
    }
}
