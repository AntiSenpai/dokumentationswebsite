<?php

namespace App\Repository;

use App\Entity\Zeiterfassung;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;

/**
 * @extends ServiceEntityRepository<Zeiterfassung>
 *
 * @method Zeiterfassung|null find($id, $lockMode = null, $lockVersion = null)
 * @method Zeiterfassung|null findOneBy(array $criteria, array $orderBy = null)
 * @method Zeiterfassung[]    findAll()
 * @method Zeiterfassung[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ZeiterfassungRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Zeiterfassung::class);
    }

    public function getCurrentEntryByUser(User $user, $type = 'Arbeit')
    {
        return $this->createQueryBuilder('e')
            ->where('e.user = :user')
            ->andWhere('e.typ = :type')
            ->andWhere('e.endzeitpunkt IS NULL')
            ->setParameters([
                'user' => $user,
                'type' => $type
            ])
            ->orderBy('e.startzeitpunkt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

    }

//    /**
//     * @return Zeiterfassung[] Returns an array of Zeiterfassung objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('z')
//            ->andWhere('z.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('z.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Zeiterfassung
//    {
//        return $this->createQueryBuilder('z')
//            ->andWhere('z.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
