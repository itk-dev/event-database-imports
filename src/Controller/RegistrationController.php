<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Types\UserRoles;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

#[Route('/admin/register')]
class RegistrationController extends AbstractDashboardController
{
    public function __construct(
        private readonly EmailVerifier $emailVerifier,
        private readonly UserRepository $userRepository,
        private readonly string $siteSendFromEmail,
        private readonly string $siteName,
    ) {
    }

    #[Route('/admin')]
    public function index(): Response
    {
        return $this->redirectToRoute('admin');
    }

    #[Route('/', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $user->setTermsAcceptedAt(new \DateTimeImmutable());
            $user->setRoles([UserRoles::ROLE_API_USER]);
            $user->setCreatedBy($user->getName());
            $user->setUpdatedBy($user->getName());

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address($this->siteSendFromEmail, $this->siteName))
                    ->to($user->getMail())
                    ->subject($translator->trans('registration.page.confirm_email', [], 'messages'))
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            return $this->render('registration/confirm.html.twig', [
                'page_title' => new TranslatableMessage('registration.page.confirm_email'),
                'email' => $user->getMail(),
            ]);
        }

        if ($form->isSubmitted()) {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('danger', $error->getMessage());
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'page_title' => new TranslatableMessage('registration.page.please_register'),
        ]);
    }

    #[Route('/verify-email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $id = $request->query->get('id');

        // Verify the user id exists and is not null
        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $this->userRepository->find($id);

        // Ensure the user exists in persistence layer
        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', new TranslatableMessage('registration.page.email_verified'));

        return $this->redirectToRoute('app_admin_login');
    }
}
