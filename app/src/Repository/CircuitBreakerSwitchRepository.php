<?php

namespace App\Repository;

use App\Entity\CircuitBreakerSwitch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CircuitBreakerSwitch|null find($id, $lockMode = null, $lockVersion = null)
 * @method CircuitBreakerSwitch|null findOneBy(array $criteria, array $orderBy = null)
 * @method CircuitBreakerSwitch[]    findAll()
 * @method CircuitBreakerSwitch[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CircuitBreakerSwitchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CircuitBreakerSwitch::class);
    }

    // /**
    //  * @return CircuitBreakerSwitch[] Returns an array of CircuitBreakerSwitch objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CircuitBreakerSwitch
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
