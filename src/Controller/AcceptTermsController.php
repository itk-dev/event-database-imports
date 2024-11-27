<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AcceptTermsFormType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\TranslatableMessage;

#[Route('/admin/accept-terms')]
class AcceptTermsController extends AbstractDashboardController
{
    #[Route('/admin')]
    public function index(): Response
    {
        return $this->redirectToRoute('admin');
    }

    #[Route('/', name: 'app_accept_terms')]
    public function acceptTerms(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);

        $form = $this->createForm(AcceptTermsFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setTermsAcceptedAt(new \DateTimeImmutable());

            $entityManager->flush();

            return $this->redirectToRoute('admin');
        }

        return $this->render('app/accept_terms/accept_terms_form.html.twig', [
            'registrationForm' => $form->createView(),
            'page_title' => new TranslatableMessage('terms.page.accept_terms'),
        ]);
    }
}
