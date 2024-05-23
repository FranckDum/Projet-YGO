<?php

namespace App\Repository;

use App\Entity\AdresseFacturationCommande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AdresseFacturationCommande>
 *
 * @method AdresseFacturationCommande|null find($id, $lockMode = null, $lockVersion = null)
 * @method AdresseFacturationCommande|null findOneBy(array $criteria, array $orderBy = null)
 * @method AdresseFacturationCommande[]    findAll()
 * @method AdresseFacturationCommande[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdresseFacturationCommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdresseFacturationCommande::class);
    }

    //    /**
    //     * @return AdresseFacturationCommande[] Returns an array of AdresseFacturationCommande objects
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

    //    public function findOneBySomeField($value): ?AdresseFacturationCommande
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
