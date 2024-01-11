<?php

namespace App\Repository;

use App\Entity\DocumentationEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DocumentationEntry>
 *
 * @method DocumentationEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method DocumentationEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method DocumentationEntry[]    findAll()
 * @method DocumentationEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentationEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DocumentationEntry::class);
    }

//    /**
//     * @return DocumentationEntry[] Returns an array of DocumentationEntry objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?DocumentationEntry
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
