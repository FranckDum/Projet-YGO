<?php

namespace App\Repository;

use App\Entity\TProduits;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;


/**
 * @extends ServiceEntityRepository<TProduits>
 *
 * @method TProduits|null find($id, $lockMode = null, $lockVersion = null)
 * @method TProduits|null findOneBy(array $criteria, array $orderBy = null)
 * @method TProduits[]    findAll()
 * @method TProduits[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TProduitsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TProduits::class);
    }

    public function findSimilaires(HttpClientInterface $client, string $partieNomProduit, int $produitActuelId, int $ygoId): array
    {
        // Récupérer les données de l'API pour le produit actuel
        $responseApi = $client->request("GET", "https://db.ygoprodeck.com/api/v7/cardinfo.php?language=fr&id=".$ygoId);
        $responseApiArray = $responseApi->toArray();
        $apiProduit = $responseApiArray['data'];
    dump("données carte selectionné comparé avec l'api:", $apiProduit);
        // Vérifier si le champ "archetype" est présent
        $archetype = isset($apiProduit[0]['archetype']) ? $apiProduit[0]['archetype'] : null;
        dump('archetype:', $archetype);
        $result = [];
    
        // Rechercher par archetype
        if ($archetype !== null) {
            $responseApi = $client->request("GET", "https://db.ygoprodeck.com/api/v7/cardinfo.php?language=fr&archetype=".$archetype);
            $responseApiArray = $responseApi->toArray();
            $apiArchetype = $responseApiArray['data'];
dump('archetype similaires api:', $apiArchetype);
            foreach ($apiArchetype as $key => $values) {
                $ids[$key] = $values['id'];
            }
            
            // Vérifier s'il y a des produits similaires par archetype
            if (!empty($ids)) {
                // Construire la requête pour récupérer les produits similaires par archetype
                $queryBuilder = $this->createQueryBuilder('p');
                $queryBuilder->andWhere('p.activation = :activation')
                    ->setParameter('activation', true)
                    ->andWhere($queryBuilder->expr()->in('p.ygo_id', ':ygoIds'))
                    ->setParameter('ygoIds', $ids)
                    ->andWhere('p.id != :produitActuelId')
                    ->setParameter('produitActuelId', $produitActuelId)
                    ->setMaxResults(5); // Récupérer jusqu'à 5 produits similaires
    
                $result = $queryBuilder->getQuery()->getResult();
                
                // Si des produits similaires sont trouvés, les retourner
                if (count($result) >= 5) {
                    return $result;
                }
            }
        }
    
        // Si aucun produit similaire n'a été trouvé par archetype ou si le nombre maximal de résultats est inférieur à 5, rechercher par type
        $type = isset($apiProduit[0]['type']) ? $apiProduit[0]['type'] : null;
dump('type:', $type);
        if ($type !== null) {
            $responseApi = $client->request("GET", "https://db.ygoprodeck.com/api/v7/cardinfo.php?language=fr&type=".$type);
            $responseApiArray = $responseApi->toArray();
            $apiType = $responseApiArray['data'];
dump('type similaire bdd api :', $apiType);
            foreach ($apiType as $key => $values) {
                $ids[$key] = $values['id'];
            }
            
            // Construire la requête pour récupérer les produits similaires par type
            $queryBuilder = $this->createQueryBuilder('p');
            $queryBuilder->andWhere('p.activation = :activation')
                ->setParameter('activation', true)
                ->andWhere($queryBuilder->expr()->in('p.ygo_id', ':ygoIds'))
                ->setParameter('ygoIds', $ids)
                ->andWhere('p.id != :produitActuelId')
                ->setParameter('produitActuelId', $produitActuelId)
                ->setMaxResults(5 - count($result)); // Récupérer jusqu'à (5 - nombre de résultats déjà trouvés) produits similaires
    
            $typeResults = $queryBuilder->getQuery()->getResult();
            
            // Fusionner les résultats de la recherche par type avec les résultats précédents de la recherche par archetype
            $result = array_merge($result, $typeResults);
            
            // Si le nombre total de résultats atteint ou dépasse 5, retourner les résultats
            if (count($result) >= 5) {
                return $result;
            }
        }
    
        // Si le nombre total de résultats est inférieur à 5, retourner les résultats trouvés
        return $result;
    }
    
    
    
                // break;
        // // Diviser la partieNomProduit en mots distincts
        // $mots = explode(' ', str_replace([' le ', ' la ', ' un ', ' une ', ' au ', ' aux ', ', ', ' - '], ' ', $partieNomProduit));
        // dump($partieNomProduit);
        // $queryBuilder = $this->createQueryBuilder('p');
        // $queryBuilder->andWhere('p.activation = :activation')
        //     ->setParameter('activation', true)
        //     ->andWhere('p.id != :produitActuelId')
        //     ->setParameter('produitActuelId', $produitActuelId);
        
        // // Utiliser une seule condition LIKE pour tous les mots
        // $condition = '';
        // foreach ($mots as $key => $mot) {
        //     if ($key !== 0) {
        //         $condition .= ' OR ';
        //     }
        //     $condition .= 'p.nom_produit LIKE :mot'.$key;
        //     $queryBuilder->setParameter('mot'.$key, '%'.$mot.'%');
        // }
        // // Ajouter la condition LIKE construite dynamiquement
        // dump($condition);
        // dump($mots);
        // $queryBuilder->andWhere('(' . $condition . ')');
        // dump($queryBuilder);
        // $queryBuilder->setMaxResults(6); // Récupérer jusqu'à 6 produits similaires
        
        // return $queryBuilder->getQuery()->getResult();

    public function findFilter($search = null): array
    {
        $query = $this->createQueryBuilder('p');

        if ($search) {
                $query = $query
                ->andWhere('p.nom_produit LIKE :search')
                ->orWhere('p.prix LIKE :search')
                ->setParameter('search', '%' . $search . '%'); 
        }

        return $query
            ->getQuery()
            ->getResult();
    }


//     public function index(Request $request, EntityManagerInterface $em, HttpClientInterface $client): Response
// {
//     // Récupérer la requête de l'utilisateur
//     $query = $request->query->get('q');

//     // Vérifier si la requête est vide
//     if (empty($query)) {
//         // Ne rien faire, laisser le code continuer
//     } else {
//         // Le reste du code pour la recherche
//         // ...
//     }
// }


//    /**
//     * @return TProduits[] Returns an array of TProduits objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TProduits
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
