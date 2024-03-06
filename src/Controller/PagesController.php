<?php

namespace App\Controller;

use App\Entity\TProduits;
use App\Repository\TProduitsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
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
    public function cartes(EntityManagerInterface $em, TProduitsRepository $tProduitsRepository, HttpClientInterface $client, PaginatorInterface $paginator, Request $request): Response
    {
        // $tProduit = new TProduits();

        set_time_limit(0);
        ini_set('memory_limit', '512M');

        // $api_url = "https://db.ygoprodeck.com/api/v7/cardinfo.php?language=fr";
        // // URL de l'API pour récupérer les données sur les cartes

        // // Récupérer les données JSON de l'API
        // $json_data = file_get_contents($api_url);
        // $data = json_decode($json_data, true);

        // // Boucle à travers les données JSON pour traiter chaque carte
        // foreach ($data['data'] as $card) 
        // {
        //     $ygoId = $card['id'];
        //     // Identifiant unique de la carte dans l'API Yu-Gi-Oh!

        //     // Recherche si la carte existe déjà dans la base de données
        //     $search = $tProduitsRepository->findOneBy(['ygo_id' =>  $ygoId ]);

        //     if (!$search) {
        //         // Si la carte n'existe pas dans la base de données, la créer

        //         $nom = $card['name'];
        //         // Nom de la carte

        //         // $image_url = $card['card_images'][0]['image_url_cropped'];
        //         // URL de l'image de la carte (image_cropped)

        //         $prix = $card['card_prices'][0]['cardmarket_price']; // * 0.93;
        //         // Prix de la carte récupéré à partir des données de prix de l'API, prix converti en euros.

        //         $produit = new TProduits();
        //         // Crée une nouvelle instance de l'entité TProduits

        //         $produit->setNomProduit($nom);
        //         $produit->setPrix($prix);
        //         $produit->setStock(100);
        //         $produit->setActivation(0);
        //         $produit->setYgoId($ygoId);

        //         $em->persist($produit);
        //         $em->flush();
        //         // Persiste et flush les changements dans la base de données
        //     }
        // }

                // Effectuer la requête pour récupérer les produits activés
                $produitsActifs = $tProduitsRepository->createQueryBuilder('p')
                ->where('p.activation = :activation')
                ->setParameter('activation', true)
                ->getQuery()
                ->getResult();

                $ygo_ids_arr= array_map(function ($produit) {
                    return $produit->getYgoId();
                }, $produitsActifs);

                $ygo_ids_for_API = implode(',', $ygo_ids_arr );

                $limit = $request->query->get("limit", 18); // Nombre de produits par page
                $offset = $request->query->get("offset", 0);
                

                $totalProducts = count($produitsActifs);
                $totalPages = ceil($totalProducts / $limit);

        $responseApi = $client->request("GET", "https://db.ygoprodeck.com/api/v7/cardinfo.php?language=fr&id=".$ygo_ids_for_API."&num=".$limit."&offset=".$offset);

        $responseApiArray = $responseApi->toArray();
        $meta = $responseApiArray['meta']; // Dans cette variable sera stocké toutes les infos pour ma pagination.

        $data = $responseApiArray['data'];

        // $produits = $paginator->paginate(
        //     $data, /* query NOT result */
        //     $request->query->getInt('page', 1), /*page number*/
        //     20 /*limit per page*/
        // );

        // dd($produits);

        // dd($data);
        $productsWithCards = [];
        foreach($produitsActifs as $actif){
            foreach($data as $card){
                if($actif->getYgoId() == $card["id"]){
                    $productsWithCards[] = [
                        "product" => $actif,
                        "card" => $card
                    ];
                    break;
                }
            }
        }

 // Renvoyer le template Twig avec les données de la carte
        return $this->render('pages/cartes_all.html.twig',[
            // 't_produits' => $tProduitsRepository->findAll(),
            // 'produit' => $produit,
            // 't_produit' => $tProduit,
            'productsWithCard' => $productsWithCards,
            'meta' => [
                'total_pages' => $totalPages,
            ]
            // 'meta' => $meta,
            // 'produitsActifs' => $produitsActifs,
            // 'produits' => $data
        ]);
    //     // Rend la page HTML affichant toutes les cartes

    }

    

    #[Route("pages/detail/{id}", name : "detailProduit")]
    public function detailProduit(int $id,  EntityManagerInterface $em, HttpClientInterface $client): Response
    {
        // Récupérer le produit spécifique en fonction de son ID
        $produit = $em->getRepository(TProduits::class)->find($id);
        $partieNomProduit = substr($produit->getNomProduit(), 0, 6); // Prend les 6 premiers caractères du nom
        $produitsSimilaires = $em->getRepository(TProduits::class)->findSimilaires($partieNomProduit, $id);

    
        // Si le produit n'existe pas, renvoyer une erreur
        if (!$produit) {
            // Message d'erreur
            return "Ce produit n'est plus disponible";
        }
    
        // Récupérer les données de l'API
        $responseApi = $client->request("GET", "https://db.ygoprodeck.com/api/v7/cardinfo.php?language=fr");
        $responseApiArray = $responseApi->toArray();
        $apiProduits = $responseApiArray['data'];
    
        // Renvoyer la vue Twig avec le produit sélectionné et les données de l'API
        return $this->render('pages/detail_produit.html.twig', [
            'produit' => $produit,
            'produitsSimilaires' => $produitsSimilaires,
            'apiProduits' => $apiProduits
        ]);
    }

}

