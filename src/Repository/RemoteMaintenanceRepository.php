<?php

namespace App\Repository;

use App\Entity\RemoteMaintenance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RemoteMaintenance>
 *
 * @method RemoteMaintenance|null find($id, $lockMode = null, $lockVersion = null)
 * @method RemoteMaintenance|null findOneBy(array $criteria, array $orderBy = null)
 * @method RemoteMaintenance[]    findAll()
 * @method RemoteMaintenance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RemoteMaintenanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RemoteMaintenance::class);
    }

//    /**
//     * @return RemoteMaintenance[] Returns an array of RemoteMaintenance objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?RemoteMaintenance
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
