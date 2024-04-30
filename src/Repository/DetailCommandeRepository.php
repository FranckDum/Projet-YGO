<?php

namespace App\Repository;

use App\Entity\DetailCommande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr;

/**
 * @extends ServiceEntityRepository<DetailCommande>
 *
 * @method DetailCommande|null find($id, $lockMode = null, $lockVersion = null)
 * @method DetailCommande|null findOneBy(array $criteria, array $orderBy = null)
 * @method DetailCommande[]    findAll()
 * @method DetailCommande[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DetailCommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DetailCommande::class);
    }

    public function findTop5Ventes(): array
    {
        return $this->createQueryBuilder('dc')
            ->select('t.id AS productId, t.nom_produit AS productNom, t.ygo_id AS ygoId, t.prix AS productPrix, t.stock AS productStock, COUNT(dc) AS totalVentes')
            ->leftJoin('dc.tProduits', 't')
            ->groupBy('t.id, t.nom_produit, t.ygo_id, t.prix, t.stock') // Inclure le champ id dans GROUP BY si nécessaire
            ->orderBy('totalVentes', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return DetailCommande[] Returns an array of DetailCommande objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?DetailCommande
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
