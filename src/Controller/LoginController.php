<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Translation\TranslatableMessage;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('@EasyAdmin/page/login.html.twig', [
            // parameters usually defined in Symfony login forms
            'error' => $error,
            'last_username' => $lastUsername,
            'page_title' => 'Event database log in',
            'csrf_token_intention' => 'authenticate',
            'target_path' => $this->generateUrl('admin'),
            'username_label' => new TranslatableMessage('login.user.mail'),
            'password_label' => new TranslatableMessage('login.user.password'),
            'sign_in_label' => new TranslatableMessage('login.label'),
            // @TODO: build forgot password form.
            // 'forgot_password_enabled' => true,
            // 'forgot_password_path' => $this->generateUrl('...', ['...' => '...']),
            // 'forgot_password_label' => 'Forgot your password?',
            'remember_me_enabled' => true,
            'remember_me_checked' => true,
            'remember_me_label' => new TranslatableMessage('login.page.remember'),
        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): never
    {
        throw new \Exception('Don\'t forget to activate logout in security.yaml');
    }
}
