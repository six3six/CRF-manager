<?php

namespace App\Repository;

use App\Entity\PlanningEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PlanningEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlanningEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlanningEntry[]    findAll()
 * @method PlanningEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlanningEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlanningEntry::class);
    }

    // /**
    //  * @return PlanningEntry[] Returns an array of PlanningEntry objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PlanningEntry
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
