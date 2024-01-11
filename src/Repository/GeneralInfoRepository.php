<?php

namespace App\Repository;

use App\Entity\GeneralInfo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GeneralInfo>
 *
 * @method GeneralInfo|null find($id, $lockMode = null, $lockVersion = null)
 * @method GeneralInfo|null findOneBy(array $criteria, array $orderBy = null)
 * @method GeneralInfo[]    findAll()
 * @method GeneralInfo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GeneralInfoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GeneralInfo::class);
    }

//    /**
//     * @return GeneralInfo[] Returns an array of GeneralInfo objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('g.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?GeneralInfo
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
