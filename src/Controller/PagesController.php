<?php

namespace App\Controller;

use App\Entity\TProduits;
use App\Repository\TProduitsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\DetailCommandeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class PagesController extends AbstractController
{
    
    #[Route('/', name:'accueil', methods: ['GET'])]
    public function accueil(TProduitsRepository $tProduitsRepository, DetailCommandeRepository $detailCommandeRepository, HttpClientInterface $client, SessionInterface $session): Response
    {
        // $session->set('panier', []);
        // Récupérer les données de l'API Yu-Gi-Oh
        $response = $client->request('GET', 'https://db.ygoprodeck.com/api/v7/cardinfo.php?archetype=Swordsoul');
        $data = $response->toArray();

        // Extraire les identifiants des cartes avec l'archétype Swordsoul
        $swordsoulCardIds = [];
        foreach ($data['data'] as $card) {
            $swordsoulCardIds[] = $card['id'];
        }

        // Récupérer les produits de la base de données correspondant aux identifiants des cartes Swordsoul
        $swordsoulProducts = $tProduitsRepository->findByYgoIds($swordsoulCardIds);
        // Appel de la méthode pour récupérer les 5 meilleures ventes
        $top5Ventes = $detailCommandeRepository->findTop5Ventes();

        // Récupérer les 5 derniers produits activés ajoutés en base de données
        $derniersProduitsActifs = $tProduitsRepository->findDerniersProduitsActifs(5);

        // Récupérer tous les produits actifs
        $produitsActifs = $tProduitsRepository->createQueryBuilder('p')
        ->where('p.activation = :activation')
        ->setParameter('activation', true)
        ->getQuery()
        ->getResult();

        // Obtenir le nombre total de produits actifs
        $totalProduitsActifs = count($produitsActifs);

        // Définir le nombre de produits que vous souhaitez sélectionner aléatoirement
        $nombreProduitsAleatoires = 20; // Ou tout autre nombre de produits que vous voulez

        // Vérifier si le nombre total de produits est inférieur à 20
        if ($totalProduitsActifs <= $nombreProduitsAleatoires) {
        // Si le nombre total de produits est inférieur ou égal à 20,
        // vous pouvez simplement passer tous les produits actifs à la vue
        $produitsAleatoires = $produitsActifs;
        } else {
        // Sélectionner 20 indices aléatoires dans le tableau des produits actifs
        $indicesAleatoires = array_rand($produitsActifs, $nombreProduitsAleatoires);

        // Initialiser un tableau pour stocker les produits sélectionnés aléatoirement
        $produitsAleatoires = [];

        // Parcourir les indices aléatoires et ajouter les produits correspondants au tableau
        foreach ($indicesAleatoires as $indice) {
            $produitsAleatoires[] = $produitsActifs[$indice];
        }
        }

        // Passer les données des produits sélectionnés aléatoirement à la vue
        return $this->render('pages/accueil.html.twig', [
        'cards' => $produitsAleatoires,
        'swordsoulProducts' => $swordsoulProducts,
        'top5Ventes' => $top5Ventes,
        'derniersProduitsActifs' => $derniersProduitsActifs
        ]);
    }

    #[Route('pages/all', name:'cartes_all', methods: ['GET'])]
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

                // Télécharger l'image et l'enregistrer dans un dossier
                // $image_data = file_get_contents($image_url);
                // file_put_contents("asset/Images/cropped/{$ygoId}.jpg", $image_data);
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
        
        $totalProducts = count($produitsActifs); // Obtient le nombre de produits activés
        $totalPages = ceil($totalProducts / $limit); // Arrondir à la page supérieur
        $responseApi = $client->request("GET", "https://db.ygoprodeck.com/api/v7/cardinfo.php?language=fr&id=".$ygo_ids_for_API."&num=".$limit."&offset=".$offset);
        $responseApiArray = $responseApi->toArray();
        $meta = $responseApiArray['meta']; // Dans cette variable sera stocké toutes les infos pour ma pagination.

        $data = $responseApiArray['data'];

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

    //  Renvoyer le template Twig avec les données de la carte
        return $this->render('pages/cartes_all.html.twig',[
            'productsWithCard' => $productsWithCards,
            'meta' => [
                'total_pages' => $totalPages,
            ]
        ]);
    }

    #[Route("pages/detail/{id}", name : "detailProduit")]
    public function detailProduit(int $id,  EntityManagerInterface $em, HttpClientInterface $client): Response
    {
        // Récupérer le produit spécifique en fonction de son ID
        $produit = $em->getRepository(TProduits::class)->find($id);
        $partieNomProduit = $produit->getNomProduit();
        $idYgo = $produit->getYgoId();
        $produitsSimilaires = $em->getRepository(TProduits::class)->findSimilaires($client, $partieNomProduit, $id, $idYgo);


        // Si le produit n'existe pas, renvoyer une erreur
        if (!$produit) {
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

