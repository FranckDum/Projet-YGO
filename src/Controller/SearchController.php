<?php

namespace App\Controller;

use App\Entity\TProduits;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search')]
    public function index(Request $request , EntityManagerInterface $em , HttpClientInterface $client): Response
    {
        // Récupérer la requête de l'utilisateur
        $query = $request->query->get('q');

        // Récupérer les produits correspondant à la requête
        $produits = $em->getRepository(TProduits::class)->createQueryBuilder('p')
            ->andWhere('LOWER(p.nom_produit) LIKE LOWER(:keyword)')
            ->andWhere('p.activation = :activation')
            ->setParameter('keyword', '%'.$query.'%')
            ->setParameter('activation', true)
            ->getQuery()
            ->getResult();

        // Initialiser la variable productsWithCard
        $productsWithCard = [];

        // Définir le nombre total de pages
        $totalPages = count($produits) > 0 ? ceil(count($produits) / $request->query->getInt('limit', 18)) : 1;

        // S'il y a des produits correspondants, continuer le traitement
        if (!empty($produits)) {
            $ygo_ids_arr = array_map(function ($produit) {
                return $produit->getYgoId();
            }, $produits);

            $ygo_ids_for_API = implode(',', $ygo_ids_arr );

            $limit = $request->query->get("limit", 18); // Nombre de produits par page
            $offset = $request->query->get("offset", 0);
                
            $totalProducts = count($produits); // Obtient le nombre de produits activés
            $totalPages = ceil($totalProducts / $limit); // Arrondir à la page supérieur

            $responseApi = $client->request("GET", "https://db.ygoprodeck.com/api/v7/cardinfo.php?language=fr&id=".$ygo_ids_for_API."&num=".$limit."&offset=".$offset);

            $responseApiArray = $responseApi->toArray();
            $meta = $responseApiArray['meta']; // Dans cette variable sera stocké toutes les infos pour ma pagination.

            $data = $responseApiArray['data'];

            foreach($produits as $actif){
                foreach($data as $card){
                    if($actif->getYgoId() == $card["id"]){
                        $productsWithCard[] = [
                            "product" => $actif,
                            "card" => $card
                        ];
                        break;
                    }
                }
            }
        }

        // Renvoyer le template Twig avec les données de la carte
        return $this->render('search/index.html.twig',[
            'query' => $query,
            'productsWithCard' => $productsWithCard,
            'meta' => [
                'total_pages' => $totalPages,
            ]
        ]);
    }
}
