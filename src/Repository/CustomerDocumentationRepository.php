<?php

namespace App\Repository;

use App\Entity\CustomerDocumentation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CustomerDocumentation>
 *
 * @method CustomerDocumentation|null find($id, $lockMode = null, $lockVersion = null)
 * @method CustomerDocumentation|null findOneBy(array $criteria, array $orderBy = null)
 * @method CustomerDocumentation[]    findAll()
 * @method CustomerDocumentation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomerDocumentationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomerDocumentation::class);
    }

    public function findByLocation($locationId): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.location = :locationId')
            ->setParameter('locationId', $locationId)
            ->getQuery()
            ->getResult()
        ;
    }

    public function saveCustomerDocumentation(CustomerDocumentation $documentation, bool $flush = true): void
    {
        $this->_em->persist($documentation);
        if ($flush) {
            $this->_em->flush();
        }
    }

//    /**
//     * @return CustomerDocumentation[] Returns an array of CustomerDocumentation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?CustomerDocumentation
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
