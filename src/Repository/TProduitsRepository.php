<?php

namespace App\Repository;

use App\Entity\TProduits;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
