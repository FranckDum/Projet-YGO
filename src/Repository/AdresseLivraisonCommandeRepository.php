<?php

namespace App\Repository;

use App\Entity\AdresseLivraisonCommande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AdresseLivraisonCommande>
 *
 * @method AdresseLivraisonCommande|null find($id, $lockMode = null, $lockVersion = null)
 * @method AdresseLivraisonCommande|null findOneBy(array $criteria, array $orderBy = null)
 * @method AdresseLivraisonCommande[]    findAll()
 * @method AdresseLivraisonCommande[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdresseLivraisonCommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdresseLivraisonCommande::class);
    }

    //    /**
    //     * @return AdresseLivraisonCommande[] Returns an array of AdresseLivraisonCommande objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?AdresseLivraisonCommande
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
