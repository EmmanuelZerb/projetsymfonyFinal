<?php

namespace App\Controller;

use App\Entity\Employe;
use App\Form\LoginType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LoginController extends AbstractController
{
    #[Route('/connexion', name: 'app_login')]
    public function index(Request $request, ManagerRegistry $doctrine, SessionInterface $session): Response
    {
        $form = $this->createForm(LoginType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $userRepository = $doctrine->getManager()->getRepository(Employe::class);

            $user = $userRepository->findOneBy(['login' => $data['login']]);

            if ($user && $user->getMdp() === $data['mdp']) {
                $session->set('id', $user->getId());
                $session->set('statut', $user->getStatut());

                if ($user->getStatut() === 'Employé') {
                    // Redirection vers une vue spécifique pour les Employés
                    return $this->redirectToRoute('liste_formations');
                } elseif ($user->getStatut() === 'Admin') {
                    // Redirection vers une vue spécifique pour les Admins
                    return $this->redirectToRoute('liste_formations_admin');
                } else {
                    // Redirection par défaut, si le statut n'est ni Employé ni Admin
                    return $this->redirectToRoute('login/connexion.html.twig');
                }
            } else {
                $this->addFlash('error', 'Username or password incorrect.');
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('login/connexion.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}