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
            ->where('LOWER(p.nom_produit) LIKE LOWER(:keyword)')
            ->setParameter('keyword', '%'.$query.'%')
            ->getQuery()
            ->getResult();

            // Récupérer données de l'API
            $responseApi = $client->request("GET", "https://db.ygoprodeck.com/api/v7/cardinfo.php?language=fr");

            $responseApiArray = $responseApi->toArray();
    
            $data = $responseApiArray['data'];

        // Renvoyer la vue twig avec les produits trouvés
        return $this->render('search/index.html.twig', [
            'query' => $query,
            'produits' => $produits,
            'apiProduits' => $data
        ]);
    }
}
