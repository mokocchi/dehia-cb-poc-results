<?php

namespace App\Repository;

use App\Entity\ResourceSwitch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ResourceSwitch|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResourceSwitch|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResourceSwitch[]    findAll()
 * @method ResourceSwitch[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResourceSwitchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResourceSwitch::class);
    }

    // /**
    //  * @return ResourceSwitch[] Returns an array of ResourceSwitch objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ResourceSwitch
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
