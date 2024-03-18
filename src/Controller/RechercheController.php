<?php

namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Employe;
use App\Controller\RechercheController;
use App\Entity\Inscription;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Produit;

class RechercheController extends AbstractController
{
    #[Route('/recherche', name: 'app_recherche')]
    public function index(): Response
    {
        return $this->render('recherche/index.html.twig', [
            'controller_name' => 'RechercheController',
        ]);
    }



    #[Route('/rechercheFindBy', name: 'app_recherche_findBy')]
    public function rechercheFindByAction(ManagerRegistry $doctrine)
    {
        //liste des employés de statut "0" et de nom "castaing"
        $employes = $doctrine->getRepository(Employe::class)->findBy(['nom'=>'Castaing','statut'=>0]);
        //var_dump($employes);
        //exit;
        return $this->render('recherche/employe.html.twig', array ('ensEmployes' => $employes, 'nom'=>'Castaing', 'statut'=> 0));
        
    }


    
    #[Route('/rechercheInscrEmploye', name: 'app_recherche_InscriptionEmploye')]
    public function rechercheInscrEmployeAction(Request $request, ManagerRegistry $doctrine)
    {
        $nom = $request->query->get('nom');
        $prenom = $request->query->get('prenom');
    
        if ($nom && $prenom) {
            $inscriptions = $doctrine->getManager()->getRepository(Inscription::class)->rechInscriptionsEmploye($nom, $prenom);
        } else {
            $inscriptions = []; // Ou tout autre traitement que vous jugez nécessaire en cas de champs manquants
        }
    
        return $this->render('recherche/inscription.html.twig', [
            'inscriptions' => $inscriptions,
            'nom' => $nom,
            'prenom' => $prenom
        ]);
    }
    #[Route('/rechercheInscrLibelle', name: 'app_recherche_InscriptionLibelle')]
     public function rechercheInscrLibelleAction(Request $request, ManagerRegistry $doctrine)
    {
        $libelle = $request->query->get('libelle');
     
    
        if ($libelle) {
            $inscriptions = $doctrine->getManager()->getRepository(produit::class)->find($libelle);
        } else {
            $inscriptions = []; // Ou tout autre traitement que vous jugez nécessaire en cas de champs manquants
        }
    
        return $this->render('recherche/inscriptionLibelle.html.twig', [
            'inscriptions' => $inscriptions,
            'libelle' => $libelle
        ]);
    }
}


