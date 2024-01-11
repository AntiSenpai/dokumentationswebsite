<?php

namespace App\Repository;

use App\Entity\RouterFirewall;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RouterFirewall>
 *
 * @method RouterFirewall|null find($id, $lockMode = null, $lockVersion = null)
 * @method RouterFirewall|null findOneBy(array $criteria, array $orderBy = null)
 * @method RouterFirewall[]    findAll()
 * @method RouterFirewall[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RouterFirewallRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RouterFirewall::class);
    }

//    /**
//     * @return RouterFirewall[] Returns an array of RouterFirewall objects
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

//    public function findOneBySomeField($value): ?RouterFirewall
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
