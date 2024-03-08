<?php

namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Employe;
use App\Controller\RechercheController;
use App\Entity\Inscription;

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
    public function rechercheInscrEmployeAction(ManagerRegistry $doctrine)
    {
        $inscriptions = $doctrine->getManager()->getRepository(Inscription::class)->rechInscriptionsEmploye('Castaing','toto');
        //var_dump($inscriptions);
        //exit;
        return $this->render('recherche/inscription.html.twig', array ('inscriptions' => $inscriptions, 'nom' => 'Castaing','prenom' =>'toto'));
    }
    }


