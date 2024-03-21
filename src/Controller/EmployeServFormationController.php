<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Formation;
use App\Entity\Inscription;
use App\Entity\Employe;
use App\Form\FormationType;
use App\Controller\LoginController;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class EmployeServFormationController extends AbstractController
{
    #[Route('/employe/serv/formation', name: 'app_employe_serv_formation')]
    public function index(): Response
    {
        return $this->render('employe_serv_formation/index.html.twig', [
            'controller_name' => 'EmployeServFormationController',
        ]);
    }

    #[Route('/liste-formations', name: 'liste_formations')]
    public function listeFormationsAction(ManagerRegistry $doctrine): Response
    {
        $formations = $doctrine->getRepository(Formation::class)->findAll();

        return $this->render('employe/liste_formations.html.twig', ['formations' => $formations]);
    }

    #[Route('/liste-formations-admin', name: 'liste_formations_admin')]
    public function listeFormationsAdminAction(ManagerRegistry $doctrine): Response
    {
        $formations = $doctrine->getRepository(Formation::class)->findAll();

        return $this->render('employe/liste_formations_admin.html.twig', ['formations' => $formations]);
    }

    #[Route('/creerFormation', name: 'formation_create')]
    public function create(Request $request, ManagerRegistry $doctrine): Response
    {
        $formation = new Formation();
        $form = $this->createForm(FormationType::class, $formation);

        $entityManager = $doctrine->getManager();

        try {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($formation);
                $entityManager->flush();

                $this->addFlash('success', 'Formation créée avec succès.');
                return $this->redirectToRoute('liste_formations_admin');
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue : ' . $e->getMessage());
        }

        return $this->render('formation/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/supprimer-formation/{formationId}', name: 'supprimer_formation')]
    public function supprimerFormation($formationId, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $formation = $entityManager->getRepository(Formation::class)->find($formationId);

        if (!$formation) {
            throw $this->createNotFoundException('Formation non trouvée');
        }

        // Supprimer les inscriptions liées à la formation
        $inscriptions = $entityManager->getRepository(Inscription::class)->findBy(['laformation' => $formation]);
        foreach ($inscriptions as $inscription) {
            $entityManager->remove($inscription);
        }

        // Ensuite, supprimer la formation
        $entityManager->remove($formation);
        $entityManager->flush();

        $this->addFlash('success', 'Formation supprimée avec succès.');

        return $this->redirectToRoute('liste_formations_admin');
    }

    #[Route('/modifier-formation/{formationId}', name: 'modifier_formation')]
public function modifierFormation($formationId, Request $request, ManagerRegistry $doctrine): Response
{
    $entityManager = $doctrine->getManager();
    $formation = $entityManager->getRepository(Formation::class)->find($formationId);

    if (!$formation) {
        throw $this->createNotFoundException('Formation non trouvée');
    }

    $form = $this->createForm(FormationType::class, $formation);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Enregistrer les modifications dans la base de données
        $entityManager->persist($formation);
        $entityManager->flush();

        $this->addFlash('success', 'Formation modifiée avec succès.');

        return $this->redirectToRoute('liste_formations_admin');
    }

    return $this->render('formation/modifier_formation.html.twig', [
        'form' => $form->createView(),
    ]);
}


    

    #[Route('/employe/inscription/{formationId}', name: 'employe_inscription')]
    public function inscriptionAction(Request $request, $formationId, ManagerRegistry $doctrine, SessionInterface $session): Response
    {
        // Récupère l'ID de l'employé depuis la session
        $employeId = $session->get('id');
    
        // Vérifie si l'ID de l'employé est présent dans la session
        if (!$employeId) {
            throw new \Exception('L\'utilisateur n\'est pas authentifié.');
        }
    
        // Récupère l'objet Employe depuis la base de données
        $employe = $doctrine->getRepository(Employe::class)->find($employeId);
    
        // Récupère la formation
        $formation = $doctrine->getRepository(Formation::class)->find($formationId);
    
        // Vérification si l'employé est déjà inscrit à la formation
        $inscriptionExistante = $doctrine->getRepository(Inscription::class)
            ->findOneBy(['lemploye' => $employe, 'laformation' => $formation]);
    
        // Si l'employé n'est pas encore inscrit, alors il peut s'inscrire
        if (!$inscriptionExistante) {
            $inscription = new Inscription();
            $inscription->setLemploye($employe);
            $inscription->setLaformation($formation);
            $inscription->setStatut("En attente"); // Modifier le statut ici
    
            // Validation de l'inscription
            $entityManager = $doctrine->getManager();
            $entityManager->persist($inscription);
            $entityManager->flush();
    
            $this->addFlash('success', 'Inscription en attente de validation.');
        } else {
            $this->addFlash('warning', 'Vous êtes déjà inscrit à cette formation.');
        }
    
        return $this->redirectToRoute('liste_formations');
    }
    

    #[Route('/voir-demandes-inscription', name: 'voir_demandes_inscription')]
    public function voirDemandesInscription(ManagerRegistry $doctrine): Response
    {
        // Récupérer les inscriptions en attente de validation
        $demandesInscription = $doctrine->getRepository(Inscription::class)->findBy(['statut' => 'En attente']);
    
        return $this->render('formation/voir_demandes_inscription.html.twig', [
            'inscriptions' => $demandesInscription,

        ]);
    }

    #[Route('/accepter-inscription/{inscriptionId}', name: 'accepter_inscription')]
    public function accepterInscription(Request $request, int $inscriptionId, ManagerRegistry $doctrine): Response
    {
       // Récupérer l'inscription à partir de son ID
       $inscription = $doctrine->getRepository(Inscription::class)->find($inscriptionId);

       // Effectuer le traitement pour accepter la demande d'inscription, par exemple, changer le statut
       $inscription->setStatut('En cours');

       // Enregistrer les modifications dans la base de données
       $entityManager = $doctrine->getManager();
       $entityManager->persist($inscription);
       $entityManager->flush();

       // Rediriger l'utilisateur vers une autre page
       return $this->redirectToRoute('voir_demandes_inscription');
    }

    #[Route('/refuser-inscription/{inscriptionId}', name: 'refuser_inscription')]
    public function refuserInscription(Request $request, int $inscriptionId, ManagerRegistry $doctrine): Response
    {
         // Récupérer l'inscription à partir de son ID
        $inscription = $doctrine->getRepository(Inscription::class)->find($inscriptionId);

        // Effectuer le traitement pour refuser la demande d'inscription, par exemple, changer le statut
        $inscription->setStatut('Refusée');

        // Enregistrer les modifications dans la base de données
        $entityManager = $doctrine->getManager();
        $entityManager->persist($inscription);
        $entityManager->flush();

        // Rediriger l'utilisateur vers une autre page
        return $this->redirectToRoute('voir_demandes_inscription');
    }

    #[Route('/Status_Formation',name: 'status_Formation')]
    public function StatusFormation(ManagerRegistry $doctrine,SessionInterface $session)
    {
        $inscriptions = $doctrine->getManager()->getRepository(Inscription::class)->findAll();
        $employeId = $session->get('id');
        
        if(!$inscriptions){
            $message="Aucune inscriptions en attente";
        }
        else{
            $message=null;
        }
        return $this->render('formation/status_formation.html.twig',array(
            'ensInscription'=>$inscriptions,
            'employeId' => $employeId,
            'message'=>$message));
    }

    #[Route('/Status_Formation_Accepte',name: 'status_Formation_accepte')]
    public function StatusFormationAccepte(ManagerRegistry $doctrine,SessionInterface $session)
    {
        $inscriptions = $doctrine->getManager()->getRepository(Inscription::class)->findAll();
        $employeId = $session->get('id');
      
        return $this->render('formation/status_formation_accepte.html.twig',array(
            'ensInscription'=>$inscriptions,
            'employeId' => $employeId));
    }
    #[Route('/Status_Formation_Refuse',name: 'status_Formation_refuse')]
    public function StatusFormationRefuse(ManagerRegistry $doctrine,SessionInterface $session)
    {
        $inscriptions = $doctrine->getManager()->getRepository(Inscription::class)->findAll();
        $employeId = $session->get('id');
    
        return $this->render('formation/status_formation_refuse.html.twig',array(
            'ensInscription'=>$inscriptions,
            'employeId' => $employeId));
    }

    #[Route('/Status_Formation_En_Attente',name: 'status_Formation_En_Attente')]
    public function StatusFormationEnAttente(ManagerRegistry $doctrine,SessionInterface $session)
    {
        $inscriptions = $doctrine->getManager()->getRepository(Inscription::class)->findAll();
        $employeId = $session->get('id');
 
        return $this->render('formation/status_formation_en_attente.html.twig',array(
            'ensInscription'=>$inscriptions,
            'employeId' => $employeId));
    }
    
}

