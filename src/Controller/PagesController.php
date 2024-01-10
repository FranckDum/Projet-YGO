<?php

namespace App\Controller;

use App\Entity\TProduits;
use App\Repository\TProduitsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PagesController extends AbstractController
{
    #[Route('/pages', name: 'app_pages')]
    // Annotation définissant la route pour la page d'index
    public function index(): Response
    {
        return $this->render('pages/index.html.twig', [
            'controller_name' => 'PagesController',
        ]);
    }

    #[Route('', name:'accueil')]
    // Annotation définissant la route pour la page d'accueil
    public function accueil(): Response
    {
        return $this->render('pages/accueil.html.twig', []);
    }

    #[Route('pages/all', name:'cartes_all')]
    // Annotation définissant la route pour la page affichant toutes les cartes
    public function cartes(EntityManagerInterface $em, TProduitsRepository $tProduitsRepository): Response
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $api_url = "https://db.ygoprodeck.com/api/v7/cardinfo.php";
        // URL de l'API pour récupérer les données sur les cartes

        // Récupérer les données JSON de l'API
        $json_data = file_get_contents($api_url);
        $data = json_decode($json_data, true);

        // Boucle à travers les données JSON pour traiter chaque carte
        foreach ($data['data'] as $card) 
        {
            $ygoId = $card['id'];
            // Identifiant unique de la carte dans l'API Yu-Gi-Oh!

            // Recherche si la carte existe déjà dans la base de données
            $search = $tProduitsRepository->findOneBy(['ygo_id' =>  $ygoId ]);

            if (!$search) {
                // Si la carte n'existe pas dans la base de données, la créer

                $nom = $card['name'];
                // Nom de la carte

                $image_url = $card['card_images'][0]['image_url_cropped'];
                // URL de l'image de la carte (image_cropped)

                $prix = $card['card_prices'][0]['cardmarket_price'] * 0.93;
                // Prix de la carte récupéré à partir des données de prix de l'API, avec une réduction de 7%

                $produit = new TProduits();
                // Crée une nouvelle instance de l'entité TProduits

                $produit->setNomProduit($nom);
                $produit->setPrix($prix);
                $produit->setStock(0);
                $produit->setActivation(0);
                $produit->setYgoId($ygoId);

                $em->persist($produit);
                $em->flush();
                // Persiste et flush les changements dans la base de données
            }
        }

        return $this->render('pages/cartes_all.html.twig',[]);
        // Rend la page HTML affichant toutes les cartes
    }
}
