<?php

namespace App\Controller;

use App\Entity\TProduits;
use App\Service\CartService;
use App\Form\FilterCardsFormType;
use App\Repository\TProduitsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\DetailCommandeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class PagesController extends AbstractController
{
    #[Route('/', name:'accueil', methods: ['GET'])]
    public function accueil(TProduitsRepository $tProduitsRepository, DetailCommandeRepository $detailCommandeRepository): Response
    {
        // Appel de la méthode pour récupérer les 5 meilleures ventes
        $top5Ventes = $detailCommandeRepository->findTop5Ventes();

        // Récupérer les 5 derniers produits activés ajoutés en base de données
        $derniersProduitsActifs = $tProduitsRepository->findDerniersProduitsActifs(5);

        // Récupérer d'autres données si nécessaire pour les produits activés
        $produitsActifs = $tProduitsRepository->createQueryBuilder('p')
            ->where('p.activation = :activation')
            ->setParameter('activation', true)
            ->getQuery()
            ->getResult();

        return $this->render('pages/accueil.html.twig', [
            'cards' => $produitsActifs,
            'top5Ventes' => $top5Ventes, // Passer les données des 5 meilleures ventes à la vue
            'derniersProduitsActifs' => $derniersProduitsActifs // Passer les données des 5 derniers produits activés à la vue
        ]);
    }

    #[Route('pages/all', name:'cartes_all', methods: ['GET'])]
    // Annotation définissant la route pour la page affichant toutes les cartes
    public function cartes(EntityManagerInterface $em, TProduitsRepository $tProduitsRepository, HttpClientInterface $client, PaginatorInterface $paginator, Request $request): Response
    {
        // $tProduit = new TProduits();

        // $client->request("GE")

        set_time_limit(0);
        ini_set('memory_limit', '512M');



        // 1- Récupérer toutes les cartes
        // $client

        // 2- Extraire 6 infos en fonction de chaque carte: type, atk, def, level, race, attribute
        //  Les ranger chacune dans son, tableau

        // 3- Passer chaque tableau au type du formulaire
        //  Afin de recuperer les données et les insérer dans les champs

        // 4- Afficher le formulaire du filtre sur la page

        // Recuperer les données de la requete

        // Associer au formulaire les données de la requête

        // Récupérer chaque filtre sélectionné

        // Préparer l'url en fonction du filtre choisi

        // Effectuer la requête à l'api afin de récupérer uniquement les cartes en fonction des filtres









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
                

                $totalProducts = count($produitsActifs); // Obtient le nombre de produits activés
                $totalPages = ceil($totalProducts / $limit); // Arrondir à la page supérieur

        $responseApi = $client->request("GET", "https://db.ygoprodeck.com/api/v7/cardinfo.php?language=fr&id=".$ygo_ids_for_API."&num=".$limit."&offset=".$offset);

        $responseApiArray = $responseApi->toArray();
        $meta = $responseApiArray['meta']; // Dans cette variable sera stocké toutes les infos pour ma pagination.

        $data = $responseApiArray['data'];

        $filterTypes        = [];
        $filterAtks         = [];
        $filterDefs         = [];
        $filterLevels       = [];
        $filterRaces        = [];
        $filterAttributes   = [];
        // 2- Extraire 6 infos en fonction de chaque carte: type, atk, def, level, race, attribute
        //  Les ranger chacune dans son, tableau


        // dd($responseApiArray['data']);

        foreach ($data as $card) 
        {
            if (isset($card['type']) && !empty($card['type'])) 
            {
                $filterTypes[] = $card['type'];
            }

            if (isset($card['atk']) && !empty($card['atk'])) 
            {
                $filterAtks[] = $card['atk'];
            }

            if (isset($card['def']) && !empty($card['def'])) 
            {
                $filterDefs[] = $card['def'];
            }

            if (isset($card['level']) && !empty($card['level'])) 
            {
                $filterLevels[] = $card['level'];
            }

            if (isset($card['race']) && !empty($card['race'])) 
            {
                $filterRaces[] = $card['race'];
            }

            if (isset($card['attribute']) && !empty($card['attribute'])) 
            {
                $filterAttributes[] = $card['attribute'];
            }
        }

        $form = $this->createForm(FilterCardsFormType::class, null, [
            "filterTypes"       => \array_unique($filterTypes),
            "filterAtks"        => \array_unique($filterAtks),
            "filterDefs"        => \array_unique($filterDefs),
            "filterLevels"      => \array_unique($filterLevels),
            "filterRaces"       => \array_unique($filterRaces),
            "filterAttributes"  => \array_unique($filterAttributes),
        ]);

        $form->handleRequest($request);

        // Si le formulaire est soumis et que le formulaire est valide,
        if ($form->isSubmitted() && $form->isValid()) 
        {
            // Récupérer chaque filtre sélectionné
            $formData = $form->getData();

            $url = "https://db.ygoprodeck.com/api/v7/cardinfo.php?";

            $prepareFilters = ["type","atk" ,"def", "level", "race", "attribute"];

            foreach ($formData as $key => $value) 
            {
                if (in_array($key, $prepareFilters) && !empty($value)) 
                {
                    $url = "{$url}{$key}={$value}&"; 
                }
            }

            mb_substr($url, -1, 1);
            $url = mb_substr($url, 0, -1);

            // dd($url);

            $filterResponseApi = $client->request("GET", $url);

            // dd($filterResponseApi);
            
            // dd('test');
            // dd($filterResponseApi->getStatusCode());

            if ( 400 == $filterResponseApi->getStatusCode() ) 
            {
                $this->addFlash("warning", "Aucun résultat trouvé.");
                return $this->redirectToRoute("cartes_all");
            }

            $filterResponseApiArray = $filterResponseApi->toArray();

            $productsWithCards = $paginator->paginate(
                $filterResponseApiArray['data'], /* query NOT result */
                $request->query->getInt('page', 1), /*page number*/
                10 /*limit per page*/
            );

            if($productsWithCards){
                
                $test = [];
                foreach($produitsActifs as $actif){
                    foreach($data as $card){
                        if($actif->getYgoId() == $card["id"]){
                            $test[] = [
                                "product" => $actif,
                                "card" => $card
                            ];
                            break;
                        }
                    }
                }
            }
            return $this->render('pages/cartes_all.html.twig', [
                "form" => $form->createView(),
                "productsWithCard" => $test,
                'meta' => [
                    'total_pages' => $totalPages,
                ]
            ]);


        }

        // Préparer l'url en fonction du filtre choisi

        // Effectuer la requête à l'api afin de récupérer uniquement les cartes en fonction des filtres

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
            "form" => $form->createView(),
            'meta' => [
                'total_pages' => $totalPages,
            ]
        ]);
//  Rend la page HTML affichant toutes les cartes

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

