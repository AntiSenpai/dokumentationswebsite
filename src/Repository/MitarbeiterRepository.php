<?php

namespace App\Repository;

use App\Entity\Mitarbeiter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Mitarbeiter>
 *
 * @method Mitarbeiter|null find($id, $lockMode = null, $lockVersion = null)
 * @method Mitarbeiter|null findOneBy(array $criteria, array $orderBy = null)
 * @method Mitarbeiter[]    findAll()
 * @method Mitarbeiter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MitarbeiterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mitarbeiter::class);
    }

//    /**
//     * @return Mitarbeiter[] Returns an array of Mitarbeiter objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Mitarbeiter
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
