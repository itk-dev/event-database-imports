<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\AcceptTermsFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\TranslatableMessage;

class AcceptTermsController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('/admin/accept-terms/admin')]
    public function index(): Response
    {
        return $this->redirectToRoute('admin');
    }

    #[Route('/admin/accept-terms/', name: 'app_accept_terms')]
    public function acceptTerms(Request $request): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);

        $form = $this->createForm(AcceptTermsFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setTermsAcceptedAt(\Carbon\CarbonImmutable::now());

            $this->entityManager->flush();

            return $this->redirectToRoute('admin');
        }

        return $this->render('app/accept_terms/accept_terms_form.html.twig', [
            'registrationForm' => $form->createView(),
            'page_title' => new TranslatableMessage('terms.page.accept_terms'),
        ]);
    }
}
